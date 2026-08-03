import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export const config = {
  matcher: ['/corporate/:path*'],
};

export function proxy(request: NextRequest) {
  return NextResponse.next();
}