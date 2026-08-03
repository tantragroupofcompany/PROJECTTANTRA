'use client';

import React from 'react';
import { Header } from '@/components/Header';
import { Footer } from '@/components/Footer';
import { BrandLogo } from '@/components/BrandLogo';

export default function About() {
  return (
    <div className="min-h-screen bg-gray-50 flex flex-col justify-between">
      <Header />

      <main className="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <div className="text-center max-w-3xl mx-auto mb-16">
          <div className="mb-6 inline-block">
            <BrandLogo size="xl" />
          </div>
          <h1 className="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">
            About TANTRA GROUP OF INDUSTRIES
          </h1>
          <p className="text-xl text-gray-600 leading-relaxed font-light">
            A diversified corporate conglomerate committed to empowering innovation, nurturing business ventures, and delivering unmatched value across diverse market sectors.
          </p>
        </div>

        <div className="grid md:grid-cols-2 gap-8 mb-16">
          <div className="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition">
            <div className="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-bold text-xl mb-6">
              M
            </div>
            <h2 className="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
            <p className="text-gray-600 leading-relaxed">
              To create exceptional long-term value through strategic corporate leadership, state-of-the-art infrastructure, and standardizing quality across all subsidiary operations.
            </p>
          </div>

          <div className="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition">
            <div className="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center font-bold text-xl mb-6">
              V
            </div>
            <h2 className="text-2xl font-bold text-gray-900 mb-4">Our Vision</h2>
            <p className="text-gray-600 leading-relaxed">
              To operate as a world-class global benchmark for conglomerate management, driving sustainable economic growth and setting trends in technology, commerce, and talent acquisition.
            </p>
          </div>
        </div>

        <div className="bg-white p-10 rounded-3xl border border-gray-200 shadow-sm text-center max-w-4xl mx-auto">
          <h2 className="text-2xl font-bold text-gray-900 mb-4">Executive Governance & Leadership</h2>
          <p className="text-gray-600 leading-relaxed max-w-2xl mx-auto">
            Governed under the direct vision of our Founder, Chairman, and CEO, TANTRA Group operates with strict corporate governance protocols, transparent media management, and centralized asset control.
          </p>
        </div>
      </main>

      <Footer />
    </div>
  );
}