import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { requireAuth, auditLog } from '@/lib/auth';
import { unlink } from 'fs/promises';
import path from 'path';

export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const user = await requireAuth('Founder', 'Chairman', 'CEO');
    const resolvedParams = await params;
    const mediaId = parseInt(resolvedParams.id, 10);

    if (isNaN(mediaId)) {
      return NextResponse.json({ error: 'Invalid media ID' }, { status: 400 });
    }

    const media = await prisma.mediaLibrary.findUnique({
      where: { id: mediaId },
    });

    if (!media) {
      return NextResponse.json({ error: 'Media file not found' }, { status: 404 });
    }

    // Try deleting physical file from disk
    if (media.fileUrl && media.fileUrl.startsWith('/uploads/')) {
      const diskPath = path.join(process.cwd(), 'public', media.fileUrl);
      try {
        await unlink(diskPath);
      } catch (err) {
        console.warn(`File unlink warning for ${diskPath}:`, err);
      }
    }

    // Delete record from database
    await prisma.mediaLibrary.delete({
      where: { id: mediaId },
    });

    await auditLog(
      user.id,
      'DELETE_MEDIA',
      'MediaLibrary',
      `Deleted asset ID ${mediaId} (${media.fileName})`
    );

    return NextResponse.json({
      success: true,
      message: 'Media deleted successfully',
    });
  } catch (error: any) {
    console.error('Delete media error:', error);
    if (error.message === 'Unauthorized' || error.message === 'Forbidden') {
      return NextResponse.json({ error: error.message }, { status: 403 });
    }
    return NextResponse.json({ error: 'Failed to delete media item' }, { status: 500 });
  }
}
