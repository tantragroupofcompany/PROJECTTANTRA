import { cookies } from 'next/headers';
import { prisma } from './prisma';
import bcrypt from 'bcryptjs';

const SALT_ROUNDS = 12;
const SESSION_COOKIE = 'tantra_session';
const SESSION_DURATION = 24 * 60 * 60 * 1000; // 24 hours

export interface AuthUser {
  id: number;
  username: string;
  email: string;
  role: string;
  status: string;
}

export async function hashPassword(password: string): Promise<string> {
  return bcrypt.hash(password, SALT_ROUNDS);
}

export async function verifyPassword(password: string, hash: string): Promise<boolean> {
  return bcrypt.compare(password, hash);
}

export async function createSession(user: AuthUser, ipAddress?: string, userAgent?: string): Promise<string> {
  const sessionId = crypto.randomUUID();
  
  await prisma.session.create({
    data: {
      id: sessionId,
      userId: user.id,
      ipAddress: ipAddress || '127.0.0.1',
      userAgent: userAgent || null,
      payload: JSON.stringify(user),
      lastActivity: new Date(),
    },
  });

  const cookieStore = await cookies();
  cookieStore.set(SESSION_COOKIE, sessionId, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    maxAge: SESSION_DURATION / 1000,
    path: '/',
  });

  return sessionId;
}

export async function getSession(): Promise<AuthUser | null> {
  try {
    const cookieStore = await cookies();
    const sessionId = cookieStore.get(SESSION_COOKIE)?.value;
    
    if (!sessionId) return null;

    const session = await prisma.session.findUnique({
      where: { id: sessionId },
    });

    if (!session) return null;

    const age = Date.now() - session.lastActivity.getTime();
    if (age > SESSION_DURATION) {
      await prisma.session.delete({ where: { id: sessionId } });
      return null;
    }

    // Update last activity
    await prisma.session.update({
      where: { id: sessionId },
      data: { lastActivity: new Date() },
    });

    return JSON.parse(session.payload || '{}') as AuthUser;
  } catch {
    return null;
  }
}

export async function destroySession(): Promise<void> {
  try {
    const cookieStore = await cookies();
    const sessionId = cookieStore.get(SESSION_COOKIE)?.value;
    
    if (sessionId) {
      await prisma.session.delete({ where: { id: sessionId } }).catch(() => {});
    }
    
    cookieStore.delete(SESSION_COOKIE);
  } catch {
    // Ignore errors during logout
  }
}

export async function requireAuth(...roles: string[]): Promise<AuthUser> {
  const user = await getSession();
  
  if (!user) {
    throw new Error('Unauthorized');
  }

  if (user.status !== 'Active') {
    throw new Error('Account inactive');
  }

  if (roles.length > 0 && !roles.includes(user.role)) {
    throw new Error('Forbidden');
  }

  return user;
}

export async function auditLog(userId: number, action: string, module: string, description?: string): Promise<void> {
  await prisma.auditLog.create({
    data: {
      userId,
      action,
      module,
      description: description || null,
      ipAddress: null,
      userAgent: null,
    },
  });
}