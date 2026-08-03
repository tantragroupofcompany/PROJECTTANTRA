import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { requireAuth, auditLog } from '@/lib/auth';

export const dynamic = 'force-dynamic';

const DEFAULT_BRANDING = {
  id: 1,
  logoUrl: null,
  faviconUrl: null,
  bannerUrl: null,
  primaryColor: '#4F46E5',
  secondaryColor: '#9333EA',
};

export async function GET() {
  try {
    const branding = await prisma.brandingSettings.findFirst();
    return NextResponse.json({
      success: true,
      branding: branding || DEFAULT_BRANDING,
    });
  } catch (error) {
    console.error('Fetch branding error:', error);
    return NextResponse.json({
      success: true,
      branding: DEFAULT_BRANDING,
    });
  }
}

export async function PUT(request: NextRequest) {
  try {
    const user = await requireAuth('Founder', 'Chairman', 'CEO');
    const body = await request.json();

    const {
      logo_url,
      favicon_url,
      banner_url,
      primary_color,
      secondary_color,
    } = body;

    const existing = await prisma.brandingSettings.findFirst();

    let updated;
    if (existing) {
      updated = await prisma.brandingSettings.update({
        where: { id: existing.id },
        data: {
          logoUrl: logo_url !== undefined ? logo_url : existing.logoUrl,
          faviconUrl: favicon_url !== undefined ? favicon_url : existing.faviconUrl,
          bannerUrl: banner_url !== undefined ? banner_url : existing.bannerUrl,
          primaryColor: primary_color !== undefined ? primary_color : existing.primaryColor,
          secondaryColor: secondary_color !== undefined ? secondary_color : existing.secondaryColor,
        },
      });
    } else {
      updated = await prisma.brandingSettings.create({
        data: {
          logoUrl: logo_url || null,
          faviconUrl: favicon_url || null,
          bannerUrl: banner_url || null,
          primaryColor: primary_color || '#4F46E5',
          secondaryColor: secondary_color || '#9333EA',
        },
      });
    }

    await auditLog(
      user.id,
      'UPDATE_BRANDING',
      'BrandingSettings',
      'Updated corporate branding assets & theme configuration'
    );

    return NextResponse.json({
      success: true,
      branding: updated,
    });
  } catch (error: any) {
    console.error('Update branding error:', error);
    if (error.message === 'Unauthorized' || error.message === 'Forbidden') {
      return NextResponse.json({ error: error.message }, { status: 403 });
    }
    return NextResponse.json({ error: 'Failed to update branding settings' }, { status: 500 });
  }
}
