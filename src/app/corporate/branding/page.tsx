'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { BrandLogo } from '@/components/BrandLogo';
import { useBranding } from '@/components/BrandingContext';

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
  companyBanner: string | null;
  companyFavicon: string | null;
  websiteUrl: string | null;
  status: string;
}

interface MediaItem {
  id: number;
  fileName: string;
  fileUrl: string;
  fileType: string;
}

export default function GlobalBrandingPage() {
  const router = useRouter();
  const { branding, refreshBranding } = useBranding();
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [companies, setCompanies] = useState<Company[]>([]);
  const [mediaList, setMediaList] = useState<MediaItem[]>([]);

  // Parent company branding form
  const [logoUrl, setLogoUrl] = useState('');
  const [faviconUrl, setFaviconUrl] = useState('');
  const [bannerUrl, setBannerUrl] = useState('');
  const [primaryColor, setPrimaryColor] = useState('#4F46E5');
  const [secondaryColor, setSecondaryColor] = useState('#9333EA');

  const [savingBranding, setSavingBranding] = useState(false);
  const [brandingNotice, setBrandingNotice] = useState('');

  // Selected company management
  const [selectedCompanyId, setSelectedCompanyId] = useState<number | null>(null);
  const [companyLogo, setCompanyLogo] = useState('');
  const [companyBanner, setCompanyBanner] = useState('');
  const [companyFavicon, setCompanyFavicon] = useState('');
  const [savingCompany, setSavingCompany] = useState(false);
  const [companyNotice, setCompanyNotice] = useState('');

  const isAdmin = user && ['Founder', 'Chairman', 'CEO'].includes(user.role);

  useEffect(() => {
    checkAuth();
    fetchCompanies();
    fetchMedia();
  }, []);

  useEffect(() => {
    if (branding) {
      setLogoUrl(branding.logoUrl || '');
      setFaviconUrl(branding.faviconUrl || '');
      setBannerUrl(branding.bannerUrl || '');
      setPrimaryColor(branding.primaryColor || '#4F46E5');
      setSecondaryColor(branding.secondaryColor || '#9333EA');
    }
  }, [branding]);

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
      if (res.ok && data.data) {
        setCompanies(data.data);
        if (data.data.length > 0) {
          selectCompany(data.data[0]);
        }
      }
    } catch {}
  };

  const fetchMedia = async () => {
    try {
      const res = await fetch('/api/media');
      const data = await res.json();
      if (res.ok) setMediaList(data.media || []);
    } catch {}
  };

  const selectCompany = (comp: Company) => {
    setSelectedCompanyId(comp.id);
    setCompanyLogo(comp.companyLogo || '');
    setCompanyBanner(comp.companyBanner || '');
    setCompanyFavicon(comp.companyFavicon || '');
  };

  const handleLogout = async () => {
    await fetch('/api/auth/logout', { method: 'POST' });
    router.push('/corporate/login');
  };

  const handleSaveParentBranding = async (e: React.FormEvent) => {
    e.preventDefault();
    setSavingBranding(true);
    setBrandingNotice('');

    try {
      const res = await fetch('/api/branding', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          logo_url: logoUrl || null,
          favicon_url: faviconUrl || null,
          banner_url: bannerUrl || null,
          primary_color: primaryColor,
          secondary_color: secondaryColor,
        }),
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Failed to update branding');

      await refreshBranding();
      setBrandingNotice('Parent Company Branding updated successfully across entire ecosystem!');
      setTimeout(() => setBrandingNotice(''), 4000);
    } catch (err: any) {
      alert(err.message || 'Save error');
    } finally {
      setSavingBranding(false);
    }
  };

  const handleSaveCompanyBranding = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedCompanyId) return;

    setSavingCompany(true);
    setCompanyNotice('');

    try {
      const res = await fetch('/api/companies', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: selectedCompanyId,
          companyLogo: companyLogo || null,
          companyBanner: companyBanner || null,
          companyFavicon: companyFavicon || null,
        }),
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Failed to update company logo');

      await fetchCompanies();
      setCompanyNotice('Company logo and branding assets updated successfully!');
      setTimeout(() => setCompanyNotice(''), 4000);
    } catch (err: any) {
      alert(err.message || 'Save error');
    } finally {
      setSavingCompany(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <p className="text-gray-500 font-medium">Loading Branding Configuration...</p>
      </div>
    );
  }

  if (!user) return null;

  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      {/* Top Header */}
      <header className="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <Link href="/" className="flex items-center space-x-3">
              <BrandLogo size="md" />
            </Link>
            <div className="flex items-center space-x-4">
              <div className="text-right hidden sm:block">
                <span className="text-sm font-semibold text-gray-900 block">{user.username}</span>
                <span className="text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full font-medium">{user.role}</span>
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

      {/* Navigation Submenu */}
      <nav className="bg-white border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex space-x-8 overflow-x-auto">
            <Link href="/corporate/dashboard" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
              Dashboard Overview
            </Link>
            <Link href="/corporate/companies" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
              Company Management
            </Link>
            <Link href="/corporate/media" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
              Media Library
            </Link>
            <Link href="/corporate/branding" className="border-indigo-600 text-indigo-600 py-3.5 px-1 border-b-2 font-semibold text-sm whitespace-nowrap">
              Branding Settings
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

      {/* Main Content Area */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Global Branding Settings</h1>
          <p className="text-gray-600 text-sm mt-1">
            Update parent company logos, favicons, corporate hero banners, and subsidiary logos across the website in real-time.
          </p>
        </div>

        {/* PARENT COMPANY BRANDING SECTION */}
        <section className="bg-white rounded-3xl border border-gray-200 shadow-sm p-8">
          <div className="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-6 mb-8 gap-4">
            <div>
              <span className="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">
                Parent Company
              </span>
              <h2 className="text-2xl font-bold text-gray-900 mt-2">TANTRA GROUP OF INDUSTRIES</h2>
            </div>
            <Link
              href="/corporate/media"
              className="text-xs text-indigo-600 hover:text-indigo-800 font-semibold bg-gray-50 border border-gray-200 px-4 py-2 rounded-xl text-center"
            >
              Browse Media Library Assets
            </Link>
          </div>

          {brandingNotice && (
            <div className="bg-green-50 text-green-800 p-4 rounded-2xl text-sm font-medium mb-6 border border-green-200">
              ✓ {brandingNotice}
            </div>
          )}

          <form onSubmit={handleSaveParentBranding} className="space-y-8">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {/* Logo URL */}
              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                  Parent Logo Asset URL
                </label>
                <div className="flex space-x-2">
                  <input
                    type="text"
                    placeholder="/uploads/logos/logo.png"
                    value={logoUrl}
                    onChange={(e) => setLogoUrl(e.target.value)}
                    disabled={!isAdmin}
                    className="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  />
                  {mediaList.filter((m) => m.fileType === 'logos').length > 0 && isAdmin && (
                    <select
                      onChange={(e) => setLogoUrl(e.target.value)}
                      className="px-3 py-2.5 rounded-xl border border-gray-300 text-xs bg-gray-50 max-w-[140px]"
                    >
                      <option value="">Select Logo...</option>
                      {mediaList
                        .filter((m) => m.fileType === 'logos')
                        .map((m) => (
                          <option key={m.id} value={m.fileUrl}>
                            {m.fileName}
                          </option>
                        ))}
                    </select>
                  )}
                </div>
                <p className="text-xs text-gray-500 mt-1">Reflects automatically on Header, Footer, Cards, & Dashboards.</p>
              </div>

              {/* Favicon URL */}
              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                  Parent Favicon Asset URL
                </label>
                <input
                  type="text"
                  placeholder="/uploads/logos/favicon.png"
                  value={faviconUrl}
                  onChange={(e) => setFaviconUrl(e.target.value)}
                  disabled={!isAdmin}
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                />
                <p className="text-xs text-gray-500 mt-1">Updates browser tab icon automatically.</p>
              </div>

              {/* Corporate Banner URL */}
              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                  Corporate Hero Banner URL
                </label>
                <div className="flex space-x-2">
                  <input
                    type="text"
                    placeholder="/uploads/banners/hero-banner.jpg"
                    value={bannerUrl}
                    onChange={(e) => setBannerUrl(e.target.value)}
                    disabled={!isAdmin}
                    className="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  />
                  {mediaList.filter((m) => m.fileType === 'banners').length > 0 && isAdmin && (
                    <select
                      onChange={(e) => setBannerUrl(e.target.value)}
                      className="px-3 py-2.5 rounded-xl border border-gray-300 text-xs bg-gray-50 max-w-[140px]"
                    >
                      <option value="">Select Banner...</option>
                      {mediaList
                        .filter((m) => m.fileType === 'banners')
                        .map((m) => (
                          <option key={m.id} value={m.fileUrl}>
                            {m.fileName}
                          </option>
                        ))}
                    </select>
                  )}
                </div>
                <p className="text-xs text-gray-500 mt-1">Displayed on corporate website landing hero.</p>
              </div>

              {/* Brand Colors */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Primary Color
                  </label>
                  <div className="flex items-center space-x-2">
                    <input
                      type="color"
                      value={primaryColor}
                      onChange={(e) => setPrimaryColor(e.target.value)}
                      disabled={!isAdmin}
                      className="w-10 h-10 rounded-lg cursor-pointer border border-gray-300 p-0.5"
                    />
                    <input
                      type="text"
                      value={primaryColor}
                      onChange={(e) => setPrimaryColor(e.target.value)}
                      disabled={!isAdmin}
                      className="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs font-mono"
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Secondary Color
                  </label>
                  <div className="flex items-center space-x-2">
                    <input
                      type="color"
                      value={secondaryColor}
                      onChange={(e) => setSecondaryColor(e.target.value)}
                      disabled={!isAdmin}
                      className="w-10 h-10 rounded-lg cursor-pointer border border-gray-300 p-0.5"
                    />
                    <input
                      type="text"
                      value={secondaryColor}
                      onChange={(e) => setSecondaryColor(e.target.value)}
                      disabled={!isAdmin}
                      className="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs font-mono"
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* Live Preview Box */}
            <div className="bg-gray-50 p-6 rounded-2xl border border-gray-200">
              <h4 className="text-xs font-bold text-gray-700 uppercase tracking-wider mb-4">
                Parent Logo & Branding Live Preview
              </h4>
              <div className="bg-white p-6 rounded-xl border border-gray-200 flex flex-col md:flex-row items-center justify-between gap-6">
                <BrandLogo logoUrl={logoUrl || null} size="lg" />
                <div className="flex items-center space-x-3">
                  <div className="px-4 py-2 rounded-xl text-white text-xs font-semibold" style={{ backgroundColor: primaryColor }}>
                    Primary Accent
                  </div>
                  <div className="px-4 py-2 rounded-xl text-white text-xs font-semibold" style={{ backgroundColor: secondaryColor }}>
                    Secondary Accent
                  </div>
                </div>
              </div>
            </div>

            {isAdmin && (
              <div className="flex justify-end">
                <button
                  type="submit"
                  disabled={savingBranding}
                  className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-xl shadow-lg shadow-indigo-200 transition disabled:opacity-50"
                >
                  {savingBranding ? 'Saving Global Branding...' : 'Save Global Parent Branding'}
                </button>
              </div>
            )}
          </form>
        </section>

        {/* SUBSIDIARY COMPANY LOGO MANAGEMENT SECTION */}
        <section className="bg-white rounded-3xl border border-gray-200 shadow-sm p-8">
          <div className="border-b border-gray-100 pb-6 mb-8">
            <span className="text-xs font-bold uppercase tracking-wider text-purple-600 bg-purple-50 px-3 py-1 rounded-full">
              Subsidiaries & Future Ventures
            </span>
            <h2 className="text-2xl font-bold text-gray-900 mt-2">Company Logo & Asset Management</h2>
            <p className="text-gray-500 text-sm">Assign dedicated logos, banners, and favicons per company.</p>
          </div>

          {companyNotice && (
            <div className="bg-green-50 text-green-800 p-4 rounded-2xl text-sm font-medium mb-6 border border-green-200">
              ✓ {companyNotice}
            </div>
          )}

          {/* Company Selection Selector */}
          <div className="flex flex-wrap gap-3 mb-8">
            {companies.map((comp) => (
              <button
                key={comp.id}
                type="button"
                onClick={() => selectCompany(comp)}
                className={`px-5 py-3 rounded-2xl text-sm font-bold transition flex items-center space-x-3 ${
                  selectedCompanyId === comp.id
                    ? 'bg-purple-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                <span>{comp.companyName}</span>
                <span className={`text-[10px] px-2 py-0.5 rounded-full ${selectedCompanyId === comp.id ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600'}`}>
                  {comp.companyCode}
                </span>
              </button>
            ))}
          </div>

          {selectedCompanyId && (
            <form onSubmit={handleSaveCompanyBranding} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Company Logo URL
                  </label>
                  <input
                    type="text"
                    placeholder="/uploads/logos/shoptantra.png"
                    value={companyLogo}
                    onChange={(e) => setCompanyLogo(e.target.value)}
                    disabled={!isAdmin}
                    className="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Company Banner URL
                  </label>
                  <input
                    type="text"
                    placeholder="/uploads/banners/shoptantra-banner.jpg"
                    value={companyBanner}
                    onChange={(e) => setCompanyBanner(e.target.value)}
                    disabled={!isAdmin}
                    className="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Company Favicon URL
                  </label>
                  <input
                    type="text"
                    placeholder="/uploads/logos/shoptantra-favicon.png"
                    value={companyFavicon}
                    onChange={(e) => setCompanyFavicon(e.target.value)}
                    disabled={!isAdmin}
                    className="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"
                  />
                </div>
              </div>

              {/* Company Logo Live Preview */}
              <div className="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <h4 className="text-xs font-bold text-gray-700 uppercase tracking-wider mb-4">
                  Company Card & Logo Preview
                </h4>
                <div className="bg-white p-6 rounded-2xl border border-gray-200 flex items-center justify-between">
                  <BrandLogo
                    logoUrl={companyLogo || null}
                    fallbackText={companies.find((c) => c.id === selectedCompanyId)?.companyName || 'Company'}
                    size="lg"
                  />
                </div>
              </div>

              {isAdmin && (
                <div className="flex justify-end">
                  <button
                    type="submit"
                    disabled={savingCompany}
                    className="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-3 rounded-xl shadow-lg shadow-purple-200 transition disabled:opacity-50"
                  >
                    {savingCompany ? 'Updating Company Logo...' : 'Save Company Branding'}
                  </button>
                </div>
              )}
            </form>
          )}
        </section>
      </main>
    </div>
  );
}
