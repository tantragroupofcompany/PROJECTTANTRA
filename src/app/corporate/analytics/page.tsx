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

interface Company {
  id: number;
  companyName: string;
  companyCode: string;
  status: string;
}

export default function Analytics() {
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);
  const [companies, setCompanies] = useState<Company[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
    fetchCompanies();
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

  const fetchCompanies = async () => {
    try {
      const res = await fetch('/api/companies');
      const data = await res.json();
      if (res.ok) setCompanies(data.data || []);
    } catch {}
  };

  const handleLogout = async () => {
    await fetch('/api/auth/logout', { method: 'POST' });
    router.push('/corporate/login');
  };

  if (loading) return <div className="min-h-screen bg-gray-50 flex items-center justify-center"><p className="text-gray-500">Loading...</p></div>;
  if (!user) return null;

  const liveCount = companies.filter((c) => c.status === 'Live').length;
  const draftCount = companies.length - liveCount;

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
            <Link href="/corporate/reports" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">Reports</Link>
            <Link href="/corporate/analytics" className="border-indigo-600 text-indigo-600 py-3.5 px-1 border-b-2 font-semibold text-sm whitespace-nowrap">Analytics</Link>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Analytics Center</h1>
          <p className="text-sm text-gray-500 mt-1">Platform and company performance insights</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <h3 className="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Companies</h3>
            <p className="text-3xl font-bold text-gray-900 mt-2">{companies.length}</p>
          </div>
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <h3 className="text-gray-500 text-xs font-semibold uppercase tracking-wider">Live Ventures</h3>
            <p className="text-3xl font-bold text-green-600 mt-2">{liveCount}</p>
          </div>
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <h3 className="text-gray-500 text-xs font-semibold uppercase tracking-wider">In Development</h3>
            <p className="text-3xl font-bold text-amber-600 mt-2">{draftCount}</p>
          </div>
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <h3 className="text-gray-500 text-xs font-semibold uppercase tracking-wider">Leadership Team</h3>
            <p className="text-3xl font-bold text-indigo-600 mt-2">3</p>
          </div>
        </div>

        <div className="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-bold text-gray-900 mb-4">Platform Overview</h2>
          <div className="space-y-4">
            {companies.map((company) => (
              <div key={company.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <div>
                  <h3 className="text-sm font-semibold text-gray-900">{company.companyName}</h3>
                  <code className="text-xs font-mono text-gray-500">{company.companyCode}</code>
                </div>
                <span className={`px-2.5 py-1 text-xs rounded-full font-medium ${company.status === 'Live' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'}`}>
                  {company.status}
                </span>
              </div>
            ))}
          </div>
        </div>
      </main>
    </div>
  );
}