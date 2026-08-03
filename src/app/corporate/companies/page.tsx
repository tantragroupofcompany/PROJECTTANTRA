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
  companyDescription: string | null;
  companyLogo: string | null;
  websiteUrl: string | null;
  status: string;
  createdAt: string;
  updatedAt: string;
}

export default function CompanyManagement() {
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);
  const [companies, setCompanies] = useState<Company[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddForm, setShowAddForm] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [viewingId, setViewingId] = useState<number | null>(null);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  // Form state
  const [form, setForm] = useState({
    companyName: '',
    companyCode: '',
    companyDescription: '',
    companyLogo: '',
    websiteUrl: '',
    status: 'Draft',
  });

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

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const resetForm = () => {
    setForm({
      companyName: '',
      companyCode: '',
      companyDescription: '',
      companyLogo: '',
      websiteUrl: '',
      status: 'Draft',
    });
    setEditingId(null);
    setShowAddForm(false);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setMessage(null);

    try {
      const res = await fetch('/api/companies', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      const data = await res.json();
      if (res.ok) {
        setMessage({ type: 'success', text: 'Company created successfully' });
        resetForm();
        fetchCompanies();
      } else {
        setMessage({ type: 'error', text: data.error || 'Failed to create company' });
      }
    } catch {
      setMessage({ type: 'error', text: 'Network error. Please try again.' });
    }
  };

  const handleStatusChange = async (id: number, newStatus: string) => {
    setMessage(null);
    try {
      const res = await fetch(`/api/companies/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: newStatus }),
      });
      const data = await res.json();
      if (res.ok) {
        setMessage({ type: 'success', text: `Company status changed to ${newStatus}` });
        fetchCompanies();
      } else {
        setMessage({ type: 'error', text: data.error || 'Failed to update status' });
      }
    } catch {
      setMessage({ type: 'error', text: 'Network error. Please try again.' });
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <p className="text-gray-500">Loading Company Management...</p>
      </div>
    );
  }

  if (!user) return null;

  const viewingCompany = viewingId ? companies.find((c) => c.id === viewingId) : null;

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
            <Link href="/corporate/dashboard" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
              Dashboard Overview
            </Link>
            <Link href="/corporate/companies" className="border-indigo-600 text-indigo-600 py-3.5 px-1 border-b-2 font-semibold text-sm whitespace-nowrap">
              Company Management
            </Link>
            <Link href="/corporate/profile" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
              User Profile
            </Link>
            <Link href="/corporate/audit-logs" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
              Audit Logs
            </Link>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Company Management</h1>
            <p className="text-sm text-gray-500 mt-1">Add, edit, view, and manage company status</p>
          </div>
          <button
            onClick={() => { setShowAddForm(!showAddForm); setViewingId(null); }}
            className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-md shadow-indigo-100 transition"
          >
            {showAddForm ? 'Cancel' : '+ Add Company'}
          </button>
        </div>

        {message && (
          <div className={`px-4 py-3 rounded-xl text-sm font-medium ${message.type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
            {message.text}
          </div>
        )}

        {showAddForm && (
          <div className="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h2 className="text-lg font-bold text-gray-900 mb-6">Add New Company</h2>
            <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Company Name *</label>
                <input
                  type="text"
                  name="companyName"
                  value={form.companyName}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="e.g. ShopTantra"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Company Code *</label>
                <input
                  type="text"
                  name="companyCode"
                  value={form.companyCode}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="e.g. SHOPTANTRA"
                />
              </div>
              <div className="md:col-span-2">
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Company Description</label>
                <textarea
                  name="companyDescription"
                  value={form.companyDescription}
                  onChange={handleInputChange}
                  rows={3}
                  className="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="Describe the company's business..."
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Company Logo URL</label>
                <input
                  type="text"
                  name="companyLogo"
                  value={form.companyLogo}
                  onChange={handleInputChange}
                  className="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="https://example.com/logo.png"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Website URL</label>
                <input
                  type="text"
                  name="websiteUrl"
                  value={form.websiteUrl}
                  onChange={handleInputChange}
                  className="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="https://example.com"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Status</label>
                <select
                  name="status"
                  value={form.status}
                  onChange={handleInputChange}
                  className="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="Draft">Draft</option>
                  <option value="Live">Live</option>
                </select>
              </div>
              <div className="md:col-span-2 flex justify-end">
                <button type="submit" className="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">
                  Create Company
                </button>
              </div>
            </form>
          </div>
        )}

        {viewingCompany && (
          <div className="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div className="flex justify-between items-start mb-6">
              <div className="flex items-center space-x-4">
                <BrandLogo logoUrl={viewingCompany.companyLogo} fallbackText={viewingCompany.companyName} size="lg" />
                <div>
                  <h2 className="text-xl font-bold text-gray-900">{viewingCompany.companyName}</h2>
                  <code className="text-xs font-mono bg-gray-100 px-2 py-1 rounded font-semibold text-gray-800">{viewingCompany.companyCode}</code>
                </div>
              </div>
              <button onClick={() => setViewingId(null)} className="text-gray-400 hover:text-gray-600 text-sm font-medium">Close</button>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Description</h3>
                <p className="text-sm text-gray-700">{viewingCompany.companyDescription || 'No description provided.'}</p>
              </div>
              <div className="space-y-4">
                <div>
                  <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Website</h3>
                  {viewingCompany.websiteUrl ? (
                    <a href={viewingCompany.websiteUrl} target="_blank" rel="noopener" className="text-sm text-indigo-600 hover:underline">{viewingCompany.websiteUrl}</a>
                  ) : (
                    <p className="text-sm text-gray-500">No website provided.</p>
                  )}
                </div>
                <div>
                  <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Status</h3>
                  <span className={`px-2.5 py-1 text-xs rounded-full font-medium ${viewingCompany.status === 'Live' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'}`}>
                    {viewingCompany.status}
                  </span>
                </div>
                <div>
                  <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Created</h3>
                  <p className="text-sm text-gray-700">{new Date(viewingCompany.createdAt).toLocaleString()}</p>
                </div>
              </div>
            </div>
          </div>
        )}

        <div className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
          <div className="p-6 border-b border-gray-200">
            <h2 className="text-lg font-bold text-gray-900">All Companies</h2>
          </div>
          <div className="p-6 overflow-x-auto">
            {companies.length === 0 ? (
              <p className="text-gray-500 text-sm">No companies registered. Click "Add Company" to create one.</p>
            ) : (
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="border-b border-gray-200">
                    <th className="py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Company</th>
                    <th className="py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th className="py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th className="py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Created</th>
                    <th className="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {companies.map((c) => (
                    <tr key={c.id} className="hover:bg-gray-50/80 transition">
                      <td className="py-4 px-4">
                        <div className="flex items-center space-x-3">
                          <BrandLogo logoUrl={c.companyLogo} fallbackText={c.companyName} size="sm" />
                          <span className="text-sm font-semibold text-gray-900">{c.companyName}</span>
                        </div>
                      </td>
                      <td className="py-4 px-4">
                        <code className="text-xs font-mono bg-gray-100 px-2 py-1 rounded font-semibold text-gray-800">{c.companyCode}</code>
                      </td>
                      <td className="py-4 px-4">
                        <span className={`px-2.5 py-1 text-xs rounded-full font-medium ${c.status === 'Live' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'}`}>
                          {c.status}
                        </span>
                      </td>
                      <td className="py-4 px-4 text-sm text-gray-500">{new Date(c.createdAt).toLocaleDateString()}</td>
                      <td className="py-4 px-4">
                        <div className="flex justify-end space-x-3">
                          <button onClick={() => setViewingId(c.id)} className="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">View</button>
                          <select
                            value={c.status}
                            onChange={(e) => handleStatusChange(c.id, e.target.value)}
                            className="text-xs border border-gray-200 rounded-lg px-2 py-1 font-medium text-gray-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                          >
                            <option value="Draft">Draft</option>
                            <option value="Live">Live</option>
                          </select>
                        </div>
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