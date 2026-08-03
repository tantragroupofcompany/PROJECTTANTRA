import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';
import { BrandingProvider } from '@/components/BrandingContext';

const inter = Inter({ subsets: ['latin'] });

export const metadata: Metadata = {
  title: 'TANTRA GROUP OF INDUSTRIES',
  description: 'Corporate Ecosystem & Management System for TANTRA GROUP OF INDUSTRIES',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body className={inter.className}>
        <BrandingProvider>{children}</BrandingProvider>
      </body>
    </html>
  );
}