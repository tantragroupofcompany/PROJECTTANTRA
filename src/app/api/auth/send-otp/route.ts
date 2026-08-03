import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import nodemailer from 'nodemailer';

const OTP_EXPIRY_MINUTES = 5;

export async function POST(request: NextRequest) {
  try {
    const { email } = await request.json();

    if (!email) {
      return NextResponse.json({ error: 'Email is required' }, { status: 400 });
    }

    // Find user by email
    const user = await prisma.user.findUnique({ where: { email } });

    if (!user) {
      return NextResponse.json({ error: 'No account found with this email' }, { status: 404 });
    }

    // Only Founder, Chairman, CEO can access
    if (!['Founder', 'Chairman', 'CEO'].includes(user.role)) {
      return NextResponse.json({ error: 'Access denied' }, { status: 403 });
    }

    if (user.status !== 'Active') {
      return NextResponse.json({ error: 'Account is inactive' }, { status: 403 });
    }

    // Generate 6-digit OTP
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiresAt = new Date(Date.now() + OTP_EXPIRY_MINUTES * 60 * 1000);

    // Invalidate previous OTPs
    await prisma.otpVerification.updateMany({
      where: { email, used: false },
      data: { used: true },
    });

    // Store new OTP
    await prisma.otpVerification.create({
      data: { email, otp, expiresAt },
    });

    // Send email (in dev, log to console)
    const transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST || 'smtp.gmail.com',
      port: parseInt(process.env.SMTP_PORT || '587'),
      secure: process.env.SMTP_SECURE === 'true',
      auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS,
      },
    });

    try {
      await transporter.sendMail({
        from: process.env.SMTP_FROM || 'TANTRA GROUP OF INDUSTRIES <no-reply@tantragroup.com>',
        to: email,
        subject: 'Your Corporate Access OTP',
        html: `
          <div style="font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 12px;">
            <h2 style="color: #4F46E5; margin-bottom: 16px;">TANTRA GROUP OF INDUSTRIES</h2>
            <p style="color: #374151; font-size: 14px;">Your One-Time Password for Corporate Access:</p>
            <div style="background: #EEF2FF; padding: 16px; border-radius: 8px; text-align: center; margin: 16px 0;">
              <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #4F46E5;">${otp}</span>
            </div>
            <p style="color: #6B7280; font-size: 12px;">This OTP expires in ${OTP_EXPIRY_MINUTES} minutes. Do not share it with anyone.</p>
          </div>
        `,
      });
    } catch (emailError) {
      console.error('Email send failed (dev mode - OTP logged):', emailError);
      console.log(`[DEV] OTP for ${email}: ${otp}`);
    }

    return NextResponse.json({
      success: true,
      message: 'OTP sent successfully',
      expiresIn: OTP_EXPIRY_MINUTES * 60,
    });
  } catch (error) {
    console.error('Send OTP error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}