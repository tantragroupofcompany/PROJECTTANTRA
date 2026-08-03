'use client';

import { useEffect, useState } from 'react';
import { Header } from '@/components/Header';
import { Footer } from '@/components/Footer';
import { BrandLogo } from '@/components/BrandLogo';

interface Company {
  id: number;
  companyName: string;
  companyCode: string;
  companyDescription: string | null;
  companyLogo: string | null;
  companyBanner: string | null;
  websiteUrl: string | null;
  status: string;
}

export default function Companies() {
  const [companies, setCompanies] = useState<Company[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/companies?public=true')
      .then((res) => res.json())
      .then((data) => {
        setCompanies(data.data || []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col justify-between">
      <Header />

      <main className="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <div className="text-center max-w-3xl mx-auto mb-16">
          <h1 className="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">
            Our Corporate Portfolio & Subsidiaries
          </h1>
          <p className="text-xl text-gray-600 font-light leading-relaxed">
            Discover the high-growth companies operating under the TANTRA GROUP OF INDUSTRIES umbrella. Each company is powered by centralized media management and dynamic branding.
          </p>
        </div>

        {loading ? (
          <div className="text-center py-20">
            <p className="text-gray-500 font-medium">Loading Portfolio Companies...</p>
          </div>
        ) : companies.length === 0 ? (
          <div className="bg-white rounded-3xl p-12 text-center border border-gray-200">
            <p className="text-gray-500">No active companies available at this time.</p>
          </div>
        ) : (
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {companies.map((company) => (
              <div
                key={company.id}
                className="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
              >
                {/* Company Banner Header */}
                <div className="h-40 bg-gradient-to-r from-indigo-500 to-purple-600 relative overflow-hidden flex items-center justify-center p-4">
                  {company.companyBanner ? (
                    <img
                      src={company.companyBanner}
                      alt={`${company.companyName} Banner`}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                  ) : (
                    <div className="text-white/20 font-black text-4xl uppercase tracking-widest">
                      {company.companyCode}
                    </div>
                  )}
                  <span className="absolute top-4 right-4 bg-white/90 backdrop-blur-md text-gray-900 text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                    {company.status}
                  </span>
                </div>

                <div className="p-8 flex-1 flex flex-col justify-between">
                  <div>
                    {/* Dynamic Company Logo with Fallback */}
                    <div className="mb-6">
                      <BrandLogo
                        logoUrl={company.companyLogo}
                        fallbackText={company.companyName}
                        size="lg"
                      />
                    </div>

                    <p className="text-gray-600 text-sm leading-relaxed mb-6">
                      {company.companyDescription ||
                        'Innovative market leader delivering excellence under Tantra Group of Industries.'}
                    </p>
                  </div>

                  <div className="pt-6 border-t border-gray-100 flex items-center justify-between">
                    <span className="text-xs font-mono font-bold bg-gray-100 text-gray-700 px-2.5 py-1 rounded-lg">
                      {company.companyCode}
                    </span>
                    {company.websiteUrl && (
                      <a
                        href={company.websiteUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center space-x-1"
                      >
                        <span>Visit Platform</span>
                        <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                      </a>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </main>

      <Footer />
    </div>
  );
}