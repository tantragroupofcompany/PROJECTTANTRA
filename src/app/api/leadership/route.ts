import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { getSession, auditLog } from '@/lib/auth';

export async function GET() {
  try {
    const profiles = await prisma.leadershipProfile.findMany({
      orderBy: { displayOrder: 'asc' },
    });
    return NextResponse.json({ success: true, data: profiles });
  } catch (error) {
    console.error('Fetch leadership error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}

export async function PUT(request: NextRequest) {
  try {
    const user = await getSession();
    if (!user || !['Founder', 'Chairman', 'CEO'].includes(user.role)) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const { id, name, designation, biography, photoUrl, status, displayOrder } = await request.json();
    if (!id) {
      return NextResponse.json({ error: 'Profile ID required' }, { status: 400 });
    }

    const data: any = {};
    if (name !== undefined) data.name = name;
    if (designation !== undefined) data.designation = designation;
    if (biography !== undefined) data.biography = biography;
    if (photoUrl !== undefined) data.photoUrl = photoUrl;
    if (status !== undefined) data.status = status;
    if (displayOrder !== undefined) data.displayOrder = displayOrder;

    const profile = await prisma.leadershipProfile.update({
      where: { id },
      data,
    });

    await auditLog(user.id, 'LEADERSHIP_UPDATED', 'Leadership', `Updated leadership profile: ${name || profile.name}`);

    return NextResponse.json({ success: true, data: profile });
  } catch (error) {
    console.error('Update leadership error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}

export async function POST(request: NextRequest) {
  try {
    const user = await getSession();
    if (!user || !['Founder', 'Chairman', 'CEO'].includes(user.role)) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const { name, designation, biography, photoUrl, status, displayOrder } = await request.json();
    if (!name || !designation) {
      return NextResponse.json({ error: 'Name and designation required' }, { status: 400 });
    }

    const profile = await prisma.leadershipProfile.create({
      data: {
        name,
        designation,
        biography: biography || null,
        photoUrl: photoUrl || null,
        status: status || 'Active',
        displayOrder: displayOrder || 0,
      },
    });

    await auditLog(user.id, 'LEADERSHIP_CREATED', 'Leadership', `Created leadership profile: ${name}`);

    return NextResponse.json({ success: true, data: profile }, { status: 201 });
  } catch (error) {
    console.error('Create leadership error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}

export async function DELETE(request: NextRequest) {
  try {
    const user = await getSession();
    if (!user || !['Founder', 'Chairman', 'CEO'].includes(user.role)) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const { searchParams } = new URL(request.url);
    const id = parseInt(searchParams.get('id') || '0', 10);
    if (!id) {
      return NextResponse.json({ error: 'Profile ID required' }, { status: 400 });
    }

    await prisma.leadershipProfile.delete({ where: { id } });
    await auditLog(user.id, 'LEADERSHIP_DELETED', 'Leadership', `Deleted leadership profile ID: ${id}`);

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Delete leadership error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}