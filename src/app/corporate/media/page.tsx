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
}

interface MediaItem {
  id: number;
  fileName: string;
  fileUrl: string;
  fileType: string;
  companyId: number | null;
  uploadedBy: number | null;
  createdAt: string;
  company?: { id: number; companyName: string; companyCode: string } | null;
  uploader?: { id: number; username: string; role: string } | null;
}

const CATEGORIES = [
  { id: 'all', label: 'All Media' },
  { id: 'logos', label: 'Logos' },
  { id: 'banners', label: 'Banners' },
  { id: 'companies', label: 'Company Assets' },
  { id: 'gallery', label: 'Gallery' },
  { id: 'documents', label: 'Documents' },
];

export default function MediaLibraryPage() {
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);
  const [companies, setCompanies] = useState<Company[]>([]);
  const [mediaList, setMediaList] = useState<MediaItem[]>([]);
  const [loading, setLoading] = useState(true);

  const [activeCategory, setActiveCategory] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');
  
  // Upload modal state
  const [showUploadModal, setShowUploadModal] = useState(false);
  const [uploadFile, setUploadFile] = useState<File | null>(null);
  const [uploadCategory, setUploadCategory] = useState('logos');
  const [uploadCompanyId, setUploadCompanyId] = useState<string>('');
  const [uploading, setUploading] = useState(false);
  const [uploadError, setUploadError] = useState('');

  // Preview modal state
  const [previewMedia, setPreviewMedia] = useState<MediaItem | null>(null);
  const [copyNotice, setCopyNotice] = useState('');

  const isAdmin = user && ['Founder', 'Chairman', 'CEO'].includes(user.role);

  useEffect(() => {
    checkAuth();
    fetchCompanies();
  }, []);

  useEffect(() => {
    fetchMedia();
  }, [activeCategory, searchQuery]);

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

  const fetchMedia = async () => {
    try {
      const params = new URLSearchParams();
      if (activeCategory !== 'all') params.append('file_type', activeCategory);
      if (searchQuery) params.append('q', searchQuery);

      const res = await fetch(`/api/media?${params.toString()}`);
      const data = await res.json();
      if (res.ok) {
        setMediaList(data.media || []);
      }
    } catch (err) {
      console.error('Failed to load media:', err);
    }
  };

  const handleLogout = async () => {
    await fetch('/api/auth/logout', { method: 'POST' });
    router.push('/corporate/login');
  };

  const handleUploadSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!uploadFile) {
      setUploadError('Please select a file to upload');
      return;
    }

    setUploading(true);
    setUploadError('');

    try {
      const formData = new FormData();
      formData.append('file', uploadFile);
      formData.append('file_type', uploadCategory);
      if (uploadCompanyId) formData.append('company_id', uploadCompanyId);

      const res = await fetch('/api/media/upload', {
        method: 'POST',
        body: formData,
      });

      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.error || 'Failed to upload image');
      }

      // Reset & refresh
      setShowUploadModal(false);
      setUploadFile(null);
      fetchMedia();
    } catch (err: any) {
      setUploadError(err.message || 'Upload error');
    } finally {
      setUploading(false);
    }
  };

  const handleDeleteMedia = async (id: number) => {
    if (!confirm('Are you sure you want to delete this media asset?')) return;
    try {
      const res = await fetch(`/api/media/${id}`, { method: 'DELETE' });
      if (res.ok) {
        if (previewMedia?.id === id) setPreviewMedia(null);
        fetchMedia();
      } else {
        const data = await res.json();
        alert(data.error || 'Delete failed');
      }
    } catch {
      alert('Delete failed');
    }
  };

  const handleSetAsGlobalAsset = async (type: 'logoUrl' | 'bannerUrl' | 'faviconUrl', url: string) => {
    try {
      const payload: any = {};
      if (type === 'logoUrl') payload.logo_url = url;
      if (type === 'bannerUrl') payload.banner_url = url;
      if (type === 'faviconUrl') payload.favicon_url = url;

      const res = await fetch('/api/branding', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      if (res.ok) {
        alert(`Successfully set asset as Global Parent ${type === 'logoUrl' ? 'Logo' : type === 'bannerUrl' ? 'Banner' : 'Favicon'}!`);
      } else {
        const data = await res.json();
        alert(data.error || 'Failed to update branding asset');
      }
    } catch {
      alert('Failed to update branding asset');
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <p className="text-gray-500 font-medium">Loading Corporate Media System...</p>
      </div>
    );
  }

  if (!user) return null;

  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      {/* Top Navbar */}
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
            <Link href="/corporate/media" className="border-indigo-600 text-indigo-600 py-3.5 px-1 border-b-2 font-semibold text-sm whitespace-nowrap">
              Media Library
            </Link>
            <Link href="/corporate/branding" className="border-transparent text-gray-600 hover:text-gray-900 py-3.5 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
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
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Corporate Media Library</h1>
            <p className="text-gray-600 text-sm mt-1">
              Centralized storage for corporate logos, banners, icons, and company branding assets automatically updated site-wide.
            </p>
          </div>
          {isAdmin ? (
            <button
              onClick={() => setShowUploadModal(true)}
              className="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium shadow-md shadow-indigo-200 transition flex items-center space-x-2 shrink-0"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Upload New Media</span>
            </button>
          ) : (
            <span className="text-xs bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg border border-gray-200 font-medium">
              Read-Only Access
            </span>
          )}
        </div>

        {/* Filter and Search Bar */}
        <div className="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 mb-8 flex flex-col md:flex-row gap-4 justify-between items-center">
          <div className="flex flex-wrap gap-2 w-full md:w-auto">
            {CATEGORIES.map((cat) => (
              <button
                key={cat.id}
                onClick={() => setActiveCategory(cat.id)}
                className={`px-4 py-2 rounded-xl text-sm font-medium transition ${
                  activeCategory === cat.id
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                }`}
              >
                {cat.label}
              </button>
            ))}
          </div>

          <div className="w-full md:w-72">
            <input
              type="text"
              placeholder="Search filename..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full px-4 py-2 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
        </div>

        {/* Media Grid */}
        {mediaList.length === 0 ? (
          <div className="bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <div className="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 className="text-lg font-bold text-gray-900 mb-1">No media assets found</h3>
            <p className="text-gray-500 text-sm mb-6 max-w-sm mx-auto">
              Upload company logos, website banners, or brand graphics to manage them centrally.
            </p>
            {isAdmin && (
              <button
                onClick={() => setShowUploadModal(true)}
                className="bg-indigo-600 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition"
              >
                Upload File Now
              </button>
            )}
          </div>
        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            {mediaList.map((item) => {
              const ext = item.fileName.split('.').pop()?.toUpperCase() || 'FILE';
              return (
                <div
                  key={item.id}
                  className="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition group flex flex-col justify-between"
                >
                  <div
                    onClick={() => setPreviewMedia(item)}
                    className="h-36 bg-gray-100 relative cursor-pointer flex items-center justify-center p-3 border-b border-gray-100 overflow-hidden group-hover:bg-gray-50"
                  >
                    <img
                      src={item.fileUrl}
                      alt={item.fileName}
                      className="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105"
                      onError={(e) => {
                        (e.target as HTMLElement).style.display = 'none';
                      }}
                    />
                    <span className="absolute top-2 left-2 bg-black/60 text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase backdrop-blur-sm">
                      {ext}
                    </span>
                    {item.fileType && (
                      <span className="absolute top-2 right-2 bg-indigo-600 text-white text-[10px] font-medium px-2 py-0.5 rounded capitalize">
                        {item.fileType}
                      </span>
                    )}
                  </div>

                  <div className="p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                      <p className="text-xs font-semibold text-gray-900 truncate mb-1" title={item.fileName}>
                        {item.fileName}
                      </p>
                      {item.company && (
                        <span className="inline-block text-[11px] text-purple-700 bg-purple-50 px-2 py-0.5 rounded font-medium truncate max-w-full">
                          {item.company.companyName}
                        </span>
                      )}
                    </div>

                    <div className="mt-3 pt-2 border-t border-gray-100 flex justify-between items-center">
                      <button
                        onClick={() => setPreviewMedia(item)}
                        className="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
                      >
                        Preview & Actions
                      </button>
                      {isAdmin && (
                        <button
                          onClick={() => handleDeleteMedia(item.id)}
                          className="text-xs text-red-500 hover:text-red-700 font-medium"
                        >
                          Delete
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </main>

      {/* Upload Modal */}
      {showUploadModal && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-bold text-gray-900">Upload Media Asset</h2>
              <button
                onClick={() => setShowUploadModal(false)}
                className="text-gray-400 hover:text-gray-600 text-xl font-bold"
              >
                &times;
              </button>
            </div>

            {uploadError && (
              <div className="bg-red-50 text-red-700 p-3 rounded-xl text-xs font-medium mb-4">
                {uploadError}
              </div>
            )}

            <form onSubmit={handleUploadSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                  Select File (PNG, JPG, SVG, WEBP)
                </label>
                <input
                  type="file"
                  accept=".png,.jpg,.jpeg,.svg,.webp"
                  onChange={(e) => setUploadFile(e.target.files?.[0] || null)}
                  className="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                  Asset Category / Type
                </label>
                <select
                  value={uploadCategory}
                  onChange={(e) => setUploadCategory(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                >
                  <option value="logos">Logos</option>
                  <option value="banners">Banners</option>
                  <option value="companies">Company Assets</option>
                  <option value="gallery">Gallery</option>
                  <option value="documents">Documents</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                  Associate Company (Optional)
                </label>
                <select
                  value={uploadCompanyId}
                  onChange={(e) => setUploadCompanyId(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                >
                  <option value="">None (Parent Group Asset)</option>
                  {companies.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.companyName} ({c.companyCode})
                    </option>
                  ))}
                </select>
              </div>

              <div className="pt-4 flex justify-end space-x-3">
                <button
                  type="button"
                  onClick={() => setShowUploadModal(false)}
                  className="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={uploading}
                  className="px-5 py-2 rounded-xl text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white shadow-md disabled:opacity-50"
                >
                  {uploading ? 'Uploading...' : 'Confirm Upload'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Preview Modal */}
      {previewMedia && (
        <div className="fixed inset-0 bg-black/70 backdrop-blur-md z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 overflow-hidden max-h-[90vh] flex flex-col">
            <div className="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
              <div>
                <h3 className="font-bold text-gray-900 text-lg">{previewMedia.fileName}</h3>
                <p className="text-xs text-gray-500">
                  Uploaded {new Date(previewMedia.createdAt).toLocaleDateString()} by{' '}
                  {previewMedia.uploader?.username || 'Admin'}
                </p>
              </div>
              <button
                onClick={() => setPreviewMedia(null)}
                className="text-gray-400 hover:text-gray-600 text-2xl font-bold"
              >
                &times;
              </button>
            </div>

            <div className="bg-gray-900 rounded-2xl p-6 flex items-center justify-center min-h-[240px] max-h-[360px] overflow-hidden mb-6 relative">
              <img
                src={previewMedia.fileUrl}
                alt={previewMedia.fileName}
                className="max-h-full max-w-full object-contain rounded"
              />
            </div>

            <div className="space-y-4">
              <div className="flex items-center space-x-2">
                <input
                  type="text"
                  readOnly
                  value={previewMedia.fileUrl}
                  className="flex-1 px-3 py-2 bg-gray-100 text-xs font-mono rounded-xl border border-gray-200"
                />
                <button
                  onClick={() => {
                    navigator.clipboard.writeText(previewMedia.fileUrl);
                    setCopyNotice('URL Copied!');
                    setTimeout(() => setCopyNotice(''), 2000);
                  }}
                  className="bg-gray-900 text-white text-xs px-4 py-2 rounded-xl font-medium hover:bg-gray-800"
                >
                  {copyNotice || 'Copy URL'}
                </button>
              </div>

              {isAdmin && (
                <div className="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100 space-y-2">
                  <h4 className="text-xs font-bold text-indigo-900 uppercase tracking-wider">
                    Quick Assign to Parent Group Branding
                  </h4>
                  <div className="flex flex-wrap gap-2">
                    <button
                      onClick={() => handleSetAsGlobalAsset('logoUrl', previewMedia.fileUrl)}
                      className="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium shadow-sm transition"
                    >
                      Set as Parent Logo
                    </button>
                    <button
                      onClick={() => handleSetAsGlobalAsset('bannerUrl', previewMedia.fileUrl)}
                      className="bg-purple-600 hover:bg-purple-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium shadow-sm transition"
                    >
                      Set as Parent Banner
                    </button>
                    <button
                      onClick={() => handleSetAsGlobalAsset('faviconUrl', previewMedia.fileUrl)}
                      className="bg-slate-800 hover:bg-slate-900 text-white text-xs px-3 py-1.5 rounded-lg font-medium shadow-sm transition"
                    >
                      Set as Favicon
                    </button>
                  </div>
                </div>
              )}

              <div className="flex justify-between items-center pt-2">
                {isAdmin && (
                  <button
                    onClick={() => handleDeleteMedia(previewMedia.id)}
                    className="text-red-600 hover:text-red-800 text-xs font-semibold"
                  >
                    Delete Asset Permanently
                  </button>
                )}
                <button
                  onClick={() => setPreviewMedia(null)}
                  className="ml-auto bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-4 py-2 rounded-xl"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
