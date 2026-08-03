import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { requireAuth, auditLog } from '@/lib/auth';
import { writeFile, mkdir } from 'fs/promises';
import path from 'path';

const ALLOWED_TYPES = ['logos', 'banners', 'companies', 'gallery', 'documents'];
const ALLOWED_EXTENSIONS = ['.png', '.jpg', '.jpeg', '.svg', '.webp'];

export async function POST(request: NextRequest) {
  try {
    const user = await requireAuth('Founder', 'Chairman', 'CEO');
    
    const formData = await request.formData();
    const file = formData.get('file') as File | null;
    const fileTypeInput = (formData.get('file_type') as string) || 'gallery';
    const companyIdStr = formData.get('company_id') as string | null;

    if (!file) {
      return NextResponse.json({ error: 'No file provided' }, { status: 400 });
    }

    const fileType = ALLOWED_TYPES.includes(fileTypeInput.toLowerCase())
      ? fileTypeInput.toLowerCase()
      : 'gallery';

    const ext = path.extname(file.name).toLowerCase();
    if (!ALLOWED_EXTENSIONS.includes(ext)) {
      return NextResponse.json(
        { error: `Unsupported file format '${ext}'. Supported formats: PNG, JPG, JPEG, SVG, WEBP.` },
        { status: 400 }
      );
    }

    const bytes = await file.arrayBuffer();
    const buffer = Buffer.from(bytes);

    const safeName = file.name.replace(/[^a-zA-Z0-9._-]/g, '_');
    const filename = `${Date.now()}_${safeName}`;
    const uploadDir = path.join(process.cwd(), 'public', 'uploads', fileType);

    await mkdir(uploadDir, { recursive: true });
    const filePath = path.join(uploadDir, filename);
    await writeFile(filePath, buffer);

    const fileUrl = `/uploads/${fileType}/${filename}`;
    const companyId = companyIdStr ? parseInt(companyIdStr, 10) : null;

    const media = await prisma.mediaLibrary.create({
      data: {
        fileName: file.name,
        fileUrl,
        fileType,
        companyId: companyId && !isNaN(companyId) ? companyId : null,
        uploadedBy: user.id,
      },
      include: {
        company: {
          select: { id: true, companyName: true, companyCode: true },
        },
      },
    });

    await auditLog(
      user.id,
      'UPLOAD_MEDIA',
      'MediaLibrary',
      `Uploaded asset ${file.name} to /uploads/${fileType}/`
    );

    return NextResponse.json({
      success: true,
      media,
    });
  } catch (error: any) {
    console.error('Media upload error:', error);
    if (error.message === 'Unauthorized' || error.message === 'Forbidden') {
      return NextResponse.json({ error: error.message }, { status: 403 });
    }
    return NextResponse.json({ error: 'Failed to upload media file' }, { status: 500 });
  }
}
