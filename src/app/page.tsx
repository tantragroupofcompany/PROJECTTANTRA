'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { Header } from '@/components/Header';
import { Footer } from '@/components/Footer';
import { BrandLogo } from '@/components/BrandLogo';
import { useBranding } from '@/components/BrandingContext';

interface Company {
  id: number;
  companyName: string;
  companyCode: string;
  companyDescription: string | null;
  companyLogo: string | null;
  companyBanner: string | null;
  websiteUrl: string | null;
}

export default function Home() {
  const { branding } = useBranding();
  const [companies, setCompanies] = useState<Company[]>([]);

  useEffect(() => {
    fetch('/api/companies?public=true')
      .then((res) => res.json())
      .then((data) => {
        if (data.success && data.data) {
          setCompanies(data.data);
        }
      })
      .catch(() => {});
  }, []);

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col justify-between">
      <Header />

      <main className="flex-1">
        {/* Dynamic Hero Banner Section */}
        <section className="relative overflow-hidden bg-gradient-to-br from-indigo-900 via-gray-900 to-purple-950 text-white py-24 md:py-36">
          {branding.bannerUrl && (
            <div className="absolute inset-0 z-0 opacity-25">
              <img src={branding.bannerUrl} alt="Corporate Hero Banner" className="w-full h-full object-cover" />
            </div>
          )}
          <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div className="mb-8 inline-block bg-white/10 backdrop-blur-md px-6 py-2.5 rounded-full border border-white/20">
              <BrandLogo size="lg" className="[&_span]:text-white" />
            </div>
            <h1 className="text-5xl md:text-7xl font-extrabold mb-6 tracking-tight leading-tight">
              TANTRA{' '}
              <span className="bg-gradient-to-r from-indigo-400 via-purple-300 to-pink-400 bg-clip-text text-transparent">
                GROUP OF INDUSTRIES
              </span>
            </h1>
            <p className="text-xl md:text-2xl text-gray-300 mb-10 leading-relaxed max-w-3xl mx-auto font-light">
              A premier corporate conglomerate driving innovation, strategic growth, and operational excellence across global markets.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Link
                href="/companies"
                className="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-8 py-4 rounded-2xl hover:shadow-xl hover:shadow-indigo-500/30 transition-all font-semibold text-lg"
              >
                Explore Companies & Portfolio
              </Link>
              <Link
                href="/corporate/login"
                className="bg-white/10 backdrop-blur-md border border-white/30 text-white px-8 py-4 rounded-2xl hover:bg-white/20 transition font-semibold text-lg"
              >
                Corporate Access
              </Link>
            </div>
          </div>
        </section>

        {/* Dynamic Portfolio Companies Section */}
        <section className="bg-white py-24">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="text-center max-w-2xl mx-auto mb-16">
              <h2 className="text-3xl font-bold text-gray-900 mb-3">Our Corporate Portfolio</h2>
              <p className="text-gray-600 text-base">
                Discover the leading subsidiary brands operating under the TANTRA Group of Industries umbrella.
              </p>
            </div>

            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
              {companies.map((comp) => (
                <div
                  key={comp.id}
                  className="bg-gray-50 rounded-3xl p-8 border border-gray-200 hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
                >
                  {comp.companyBanner && (
                    <div className="h-32 -mx-8 -mt-8 mb-6 overflow-hidden rounded-t-3xl bg-gray-200">
                      <img src={comp.companyBanner} alt={comp.companyName} className="w-full h-full object-cover" />
                    </div>
                  )}
                  <div>
                    <div className="mb-4">
                      <BrandLogo logoUrl={comp.companyLogo} fallbackText={comp.companyName} size="lg" />
                    </div>
                    <p className="text-gray-600 text-sm leading-relaxed mb-6">
                      {comp.companyDescription || 'Leading corporate enterprise under Tantra Group.'}
                    </p>
                  </div>
                  {comp.websiteUrl && (
                    <a
                      href={comp.websiteUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center text-sm font-bold text-indigo-600 hover:text-indigo-800 transition group"
                    >
                      <span>Visit Official Website</span>
                      <svg className="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                      </svg>
                    </a>
                  )}
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Corporate Vision Section */}
        <section className="py-24 bg-gray-50 border-t border-gray-200">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">Our Vision & Leadership</h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
              To build a sustainable global ecosystem of transformative enterprises that set industry benchmarks for excellence, ethics, and innovation.
            </p>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
}