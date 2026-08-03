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

export default function SecurityCenter() {
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
            <Link href="/corporate/security" className="border-indigo-600 text-indigo-600 py-3.5 px-1 border-b-2 font-semibold text-sm whitespace-nowrap">Security Center</Link>
            <Link href="/corporate/audit-logs" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">Audit Logs</Link>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Security Center</h1>
          <p className="text-sm text-gray-500 mt-1">Monitor platform security and access controls</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-green-50 border border-green-200 rounded-2xl p-6">
            <div className="text-2xl mb-2">🟢</div>
            <h3 className="text-lg font-bold text-gray-900">Access Control</h3>
            <p className="text-sm text-gray-600 mt-1">RBAC enforced. Only Founder, Chairman, and CEO can access the portal.</p>
          </div>
          <div className="bg-blue-50 border border-blue-200 rounded-2xl p-6">
            <div className="text-2xl mb-2">🔐</div>
            <h3 className="text-lg font-bold text-gray-900">OTP Authentication</h3>
            <p className="text-sm text-gray-600 mt-1">Email OTP with 5-minute expiry. Session management with 24h timeout.</p>
          </div>
          <div className="bg-purple-50 border border-purple-200 rounded-2xl p-6">
            <div className="text-2xl mb-2">📋</div>
            <h3 className="text-lg font-bold text-gray-900">Audit Trail</h3>
            <p className="text-sm text-gray-600 mt-1">All corporate actions logged with user, IP, and timestamp.</p>
          </div>
        </div>
      </main>
    </div>
  );
}