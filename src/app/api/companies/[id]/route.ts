import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { getSession, auditLog } from '@/lib/auth';

export async function PUT(request: NextRequest, { params }: { params: Promise<{ id: string }> }) {
  try {
    const user = await getSession();
    if (!user || !['Founder', 'Chairman', 'CEO'].includes(user.role)) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const { id } = await params;
    const companyId = parseInt(id, 10);
    if (isNaN(companyId)) {
      return NextResponse.json({ error: 'Invalid company ID' }, { status: 400 });
    }

    const body = await request.json();
    const existing = await prisma.company.findUnique({ where: { id: companyId } });
    if (!existing) {
      return NextResponse.json({ error: 'Company not found' }, { status: 404 });
    }

    const data: any = {};
    if (body.companyName !== undefined) data.companyName = body.companyName;
    if (body.companyDescription !== undefined) data.companyDescription = body.companyDescription;
    if (body.companyLogo !== undefined) data.companyLogo = body.companyLogo;
    if (body.websiteUrl !== undefined) data.websiteUrl = body.websiteUrl;
    if (body.status !== undefined) {
      if (!['Draft', 'Live'].includes(body.status)) {
        return NextResponse.json({ error: 'Invalid status' }, { status: 400 });
      }
      data.status = body.status;
    }

    const company = await prisma.company.update({
      where: { id: companyId },
      data,
    });

    await auditLog(user.id, 'COMPANY_UPDATED', 'Company Management', `Updated company: ${company.companyName}`);

    return NextResponse.json({ success: true, data: company });
  } catch (error) {
    console.error('Update company error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}