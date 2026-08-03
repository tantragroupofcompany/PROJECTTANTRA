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
  companyLogo: string | null;
  status: string;
}

export default function Dashboard() {
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
      if (res.ok && data.user) {
        setUser(data.user);
      } else {
        router.push('/corporate/login');
      }
    } catch {
      router.push('/corporate/login');
    } finally {
      setLoading(false);
    }
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

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <p className="text-gray-500">Loading Corporate Dashboard...</p>
      </div>
    );
  }

  if (!user) return null;

  const liveCompanies = companies.filter((c) => c.status === 'Live');

  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      <header className="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <Link href="/" className="flex items-center space-x-3">
              <BrandLogo size="md" />
            </Link>
            <div className="flex items-center space-x-4">
              <div className="text-right hidden sm:block">
                <span className="text-sm font-semibold text-gray-900 block">{user.username}</span>
                <span className="text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full font-medium">
                  {user.role}
                </span>
              </div>
              <button
                onClick={handleLogout}
                className="text-sm font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>

      <nav className="bg-white border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex space-x-8 overflow-x-auto">
            <Link
              href="/corporate/dashboard"
              className="border-indigo-600 text-indigo-600 py-3.5 px-1 border-b-2 font-semibold text-sm whitespace-nowrap"
            >
              Dashboard Overview
            </Link>
            <Link
              href="/corporate/companies"
              className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
            >
              Company Management
            </Link>
            <Link
              href="/corporate/media"
              className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
            >
              Media Library
            </Link>
            <Link
              href="/corporate/branding"
              className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
            >
              Branding Settings
            </Link>
            <Link
              href="/corporate/profile"
              className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
            >
              User Profile
            </Link>
            <Link
              href="/corporate/audit-logs"
              className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
            >
              Audit Logs
            </Link>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Dashboard Overview</h1>
            <p className="text-sm text-gray-500 mt-1">Global Media & Corporate Infrastructure Command Center</p>
          </div>
          <div className="flex space-x-3">
            <Link
              href="/corporate/media"
              className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-md shadow-indigo-100 transition"
            >
              Media Library
            </Link>
            <Link
              href="/corporate/branding"
              className="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-md shadow-purple-100 transition"
            >
              Global Branding
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <h3 className="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Companies</h3>
            <p className="text-3xl font-bold text-gray-900 mt-2">{companies.length}</p>
          </div>
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <h3 className="text-gray-500 text-xs font-semibold uppercase tracking-wider">Live Ventures</h3>
            <p className="text-3xl font-bold text-green-600 mt-2">{liveCompanies.length}</p>
          </div>
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <h3 className="text-gray-500 text-xs font-semibold uppercase tracking-wider">Draft / Pending</h3>
            <p className="text-3xl font-bold text-gray-600 mt-2">{companies.length - liveCompanies.length}</p>
          </div>
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <h3 className="text-gray-500 text-xs font-semibold uppercase tracking-wider">Your Authority</h3>
            <p className="text-2xl font-bold text-indigo-600 mt-2">{user.role}</p>
          </div>
        </div>

        <div className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
          <div className="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 className="text-lg font-bold text-gray-900">Corporate Portfolio Companies</h2>
            <Link href="/corporate/branding" className="text-xs text-indigo-600 font-semibold hover:underline">
              Manage Company Logos →
            </Link>
          </div>
          <div className="p-6 overflow-x-auto">
            {companies.length === 0 ? (
              <p className="text-gray-500 text-sm">No companies registered.</p>
            ) : (
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="border-b border-gray-200">
                    <th className="py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Logo & Company</th>
                    <th className="py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th className="py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th className="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase">Branding Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {companies.map((c) => (
                    <tr key={c.id} className="hover:bg-gray-50/80 transition">
                      <td className="py-4 px-4">
                        <BrandLogo logoUrl={c.companyLogo} fallbackText={c.companyName} size="sm" />
                      </td>
                      <td className="py-4 px-4">
                        <code className="text-xs font-mono bg-gray-100 px-2 py-1 rounded font-semibold text-gray-800">
                          {c.companyCode}
                        </code>
                      </td>
                      <td className="py-4 px-4">
                        <span
                          className={`px-2.5 py-1 text-xs rounded-full font-medium ${
                            c.status === 'Live' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'
                          }`}
                        >
                          {c.status}
                        </span>
                      </td>
                      <td className="py-4 px-4 text-right">
                        <Link
                          href="/corporate/branding"
                          className="text-xs text-indigo-600 hover:text-indigo-800 font-semibold"
                        >
                          Edit Branding
                        </Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        </div>
      </main>
    </div>
  );
}