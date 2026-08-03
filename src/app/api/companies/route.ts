import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { getSession, auditLog } from '@/lib/auth';

export const dynamic = 'force-dynamic';

export async function GET(request: NextRequest) {
  const { searchParams } = new URL(request.url);
  const status = searchParams.get('status');
  const publicView = searchParams.get('public');

  const where: any = {};
  if (status) where.status = status;
  if (publicView === 'true') where.status = 'Live';

  const companies = await prisma.company.findMany({
    where,
    orderBy: { createdAt: 'desc' },
  });

  return NextResponse.json({ success: true, data: companies });
}

export async function POST(request: NextRequest) {
  try {
    const user = await getSession();
    if (!user || !['Founder', 'Chairman', 'CEO'].includes(user.role)) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const body = await request.json();
    
    if (!body.companyName || !body.companyCode) {
      return NextResponse.json({ error: 'Company name and code required' }, { status: 400 });
    }

    const existing = await prisma.company.findUnique({ where: { companyCode: body.companyCode } });
    if (existing) {
      return NextResponse.json({ error: 'Company code already exists' }, { status: 409 });
    }

    const company = await prisma.company.create({
      data: {
        companyName: body.companyName,
        companyCode: body.companyCode.toUpperCase(),
        companyDescription: body.companyDescription || null,
        companyLogo: body.companyLogo || null,
        companyBanner: body.companyBanner || null,
        companyFavicon: body.companyFavicon || null,
        websiteUrl: body.websiteUrl || null,
        status: body.status || 'Draft',
      },
    });

    await auditLog(user.id, 'COMPANY_CREATED', 'Company Management', `Created company: ${body.companyName}`);

    return NextResponse.json({ success: true, data: company }, { status: 201 });
  } catch (error) {
    console.error('Create company error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}

export async function PUT(request: NextRequest) {
  try {
    const user = await getSession();
    if (!user || !['Founder', 'Chairman', 'CEO'].includes(user.role)) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const body = await request.json();
    const { id, companyName, companyLogo, companyBanner, companyFavicon, websiteUrl, status, companyDescription } = body;

    if (!id) {
      return NextResponse.json({ error: 'Company ID required' }, { status: 400 });
    }

    const updated = await prisma.company.update({
      where: { id: parseInt(id, 10) },
      data: {
        ...(companyName && { companyName }),
        ...(companyDescription !== undefined && { companyDescription }),
        ...(companyLogo !== undefined && { companyLogo }),
        ...(companyBanner !== undefined && { companyBanner }),
        ...(companyFavicon !== undefined && { companyFavicon }),
        ...(websiteUrl !== undefined && { websiteUrl }),
        ...(status && { status }),
      },
    });

    await auditLog(user.id, 'COMPANY_UPDATED', 'Company Management', `Updated company ID ${id} branding & details`);

    return NextResponse.json({ success: true, data: updated });
  } catch (error) {
    console.error('Update company error:', error);
    return NextResponse.json({ error: 'Failed to update company' }, { status: 500 });
  }
}