'use client';

import React from 'react';
import Link from 'next/link';
import { BrandLogo } from './BrandLogo';

export function Footer() {
  return (
    <footer className="bg-gray-950 text-white border-t border-gray-800">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
          <div className="md:col-span-2 space-y-4">
            <Link href="/" className="inline-block">
              <div className="bg-white/5 p-3 rounded-xl backdrop-blur-sm inline-block">
                <BrandLogo size="md" className="[&_span]:text-white" />
              </div>
            </Link>
            <p className="text-gray-400 text-sm max-w-md leading-relaxed">
              A premier corporate conglomerate driving innovation, strategic investments, and market leadership across e-commerce, recruitment, and technology solutions.
            </p>
          </div>
          <div>
            <h4 className="text-sm font-semibold uppercase tracking-wider text-indigo-400 mb-4">Quick Links</h4>
            <ul className="space-y-2.5 text-sm text-gray-400">
              <li><Link href="/" className="hover:text-white transition">Home</Link></li>
              <li><Link href="/about" className="hover:text-white transition">About Us</Link></li>
              <li><Link href="/companies" className="hover:text-white transition">Our Portfolio</Link></li>
              <li><Link href="/contact" className="hover:text-white transition">Contact</Link></li>
            </ul>
          </div>
          <div>
            <h4 className="text-sm font-semibold uppercase tracking-wider text-indigo-400 mb-4">Corporate Access</h4>
            <ul className="space-y-2.5 text-sm text-gray-400">
              <li><Link href="/corporate/login" className="hover:text-white transition">Leadership Portal</Link></li>
              <li><Link href="/corporate/dashboard" className="hover:text-white transition">Corporate Dashboard</Link></li>
              <li><Link href="/corporate/media" className="hover:text-white transition">Media Library</Link></li>
              <li><Link href="/corporate/branding" className="hover:text-white transition">Branding Settings</Link></li>
            </ul>
          </div>
        </div>
        <div className="border-t border-gray-800/80 pt-8 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500">
          <p>&copy; {new Date().getFullYear()} TANTRA GROUP OF INDUSTRIES. All rights reserved.</p>
          <div className="flex space-x-6 mt-4 sm:mt-0">
            <span className="hover:text-gray-400 cursor-pointer">Privacy Policy</span>
            <span className="hover:text-gray-400 cursor-pointer">Terms of Governance</span>
            <span className="hover:text-gray-400 cursor-pointer">Media Guidelines</span>
          </div>
        </div>
      </div>
    </footer>
  );
}
