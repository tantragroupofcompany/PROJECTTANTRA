'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { BrandLogo } from '@/components/BrandLogo';

interface User {
  id: number;
  username: string;
  email: string;
  role: string;
}

export default function Employees() {
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      const res = await fetch('/api/auth/check');
      const data = await res.json();
      if (res.ok && data.user) setUser(data.user);
      else router.push('/corporate/login');
    } catch { router.push('/corporate/login'); }
    finally { setLoading(false); }
  };

  const handleLogout = async () => {
    await fetch('/api/auth/logout', { method: 'POST' });
    router.push('/corporate/login');
  };

  if (loading) return <div className="min-h-screen bg-gray-50 flex items-center justify-center"><p className="text-gray-500">Loading...</p></div>;
  if (!user) return null;

  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      <header className="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <Link href="/" className="flex items-center space-x-3"><BrandLogo size="md" /></Link>
            <div className="flex items-center space-x-4">
              <div className="text-right hidden sm:block">
                <span className="text-sm font-semibold text-gray-900 block">{user.username}</span>
                <span className="text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full font-medium">{user.role}</span>
              </div>
              <button onClick={handleLogout} className="text-sm font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition">Logout</button>
            </div>
          </div>
        </div>
      </header>

      <nav className="bg-white border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex space-x-8 overflow-x-auto">
            <Link href="/corporate/dashboard" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">Dashboard</Link>
            <Link href="/corporate/employees" className="border-indigo-600 text-indigo-600 py-3.5 px-1 border-b-2 font-semibold text-sm whitespace-nowrap">Employees</Link>
            <Link href="/corporate/website-builder" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">Websites</Link>
            <Link href="/corporate/reports" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">Reports</Link>
            <Link href="/corporate/security" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">Security</Link>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Employee Management</h1>
          <p className="text-sm text-gray-500 mt-1">Manage employees across all companies</p>
        </div>

        <div className="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
          <div className="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span className="text-2xl">👥</span>
          </div>
          <h2 className="text-lg font-bold text-gray-900 mb-2">Employee Directory</h2>
          <p className="text-sm text-gray-500 max-w-md mx-auto">
            The employee database is now available through the database. Add employees directly from the database to get started.
          </p>
        </div>
      </main>
    </div>
  );
}