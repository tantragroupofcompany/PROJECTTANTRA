'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import { BrandLogo } from './BrandLogo';

export function Header() {
  const [user, setUser] = useState<{ id: number; username: string; role: string } | null>(null);

  useEffect(() => {
    fetch('/api/auth/check')
      .then((res) => res.json())
      .then((data) => {
        if (data && data.user) {
          setUser(data.user);
        }
      })
      .catch(() => {});
  }, []);

  return (
    <header className="border-b border-gray-200 bg-white/90 backdrop-blur-md sticky top-0 z-50 transition-all duration-300">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-20">
          <Link href="/" className="flex items-center space-x-3 group">
            <BrandLogo size="md" />
          </Link>
          <nav className="hidden md:flex items-center space-x-8">
            <Link href="/" className="text-gray-700 hover:text-indigo-600 font-medium transition-colors">
              Home
            </Link>
            <Link href="/about" className="text-gray-700 hover:text-indigo-600 font-medium transition-colors">
              About
            </Link>
            <Link href="/companies" className="text-gray-700 hover:text-indigo-600 font-medium transition-colors">
              Companies
            </Link>
            <Link href="/contact" className="text-gray-700 hover:text-indigo-600 font-medium transition-colors">
              Contact
            </Link>
            {user ? (
              <Link
                href="/corporate/dashboard"
                className="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-5 py-2.5 rounded-xl hover:shadow-lg hover:shadow-indigo-200 transition-all font-medium text-sm flex items-center space-x-2"
              >
                <span>Dashboard</span>
                <span className="text-xs bg-white/20 px-2 py-0.5 rounded-full">{user.role}</span>
              </Link>
            ) : (
              <Link
                href="/corporate/login"
                className="bg-indigo-600 text-white px-5 py-2.5 rounded-xl hover:bg-indigo-700 transition font-medium text-sm shadow-md shadow-indigo-100"
              >
                Corporate Access
              </Link>
            )}
          </nav>
        </div>
      </div>
    </header>
  );
}
