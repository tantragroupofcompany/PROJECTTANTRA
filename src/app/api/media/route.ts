import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';

export const dynamic = 'force-dynamic';

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const fileType = searchParams.get('file_type');
    const companyIdStr = searchParams.get('company_id');
    const query = searchParams.get('q');

    const where: any = {};

    if (fileType && fileType !== 'all') {
      where.fileType = fileType.toLowerCase();
    }

    if (companyIdStr) {
      const cid = parseInt(companyIdStr, 10);
      if (!isNaN(cid)) {
        where.companyId = cid;
      }
    }

    if (query) {
      where.fileName = {
        contains: query,
        mode: 'insensitive',
      };
    }

    const mediaList = await prisma.mediaLibrary.findMany({
      where,
      orderBy: { createdAt: 'desc' },
      include: {
        company: {
          select: { id: true, companyName: true, companyCode: true },
        },
        uploader: {
          select: { id: true, username: true, role: true },
        },
      },
    });

    return NextResponse.json({
      success: true,
      media: mediaList,
    });
  } catch (error) {
    console.error('Fetch media error:', error);
    return NextResponse.json({ error: 'Failed to fetch media library items' }, { status: 500 });
  }
}
