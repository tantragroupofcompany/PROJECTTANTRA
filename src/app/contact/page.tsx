'use client';

import { useState } from 'react';
import { Header } from '@/components/Header';
import { Footer } from '@/components/Footer';
import { BrandLogo } from '@/components/BrandLogo';

export default function Contact() {
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitted(true);
  };

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col justify-between">
      <Header />

      <main className="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <div className="mb-4 inline-block">
            <BrandLogo size="lg" />
          </div>
          <h1 className="text-4xl font-extrabold text-gray-900 mb-3 tracking-tight">Contact Corporate Relations</h1>
          <p className="text-lg text-gray-600 font-light">
            Get in touch with TANTRA GROUP OF INDUSTRIES executive offices.
          </p>
        </div>

        <div className="grid md:grid-cols-2 gap-12 max-w-5xl mx-auto">
          <div className="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm">
            {submitted ? (
              <div className="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
                <div className="w-12 h-12 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl font-bold">
                  ✓
                </div>
                <h3 className="text-xl font-bold text-green-800 mb-2">Message Received</h3>
                <p className="text-green-600 text-sm">
                  Thank you for reaching out to TANTRA Group. Our communications team will respond promptly.
                </p>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-6">
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Your Full Name</label>
                  <input
                    type="text"
                    required
                    placeholder="John Doe"
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Corporate Email</label>
                  <input
                    type="email"
                    required
                    placeholder="john@company.com"
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Message / Inquiry</label>
                  <textarea
                    rows={4}
                    required
                    placeholder="How can TANTRA Group assist you?"
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm"
                  ></textarea>
                </div>
                <button
                  type="submit"
                  className="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-xl transition font-semibold text-sm shadow-md shadow-indigo-100"
                >
                  Submit Inquiry
                </button>
              </form>
            )}
          </div>

          <div className="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
              <h3 className="text-xl font-bold text-gray-900 mb-6">Global Corporate Headquarters</h3>
              <div className="space-y-4 text-sm text-gray-600">
                <div className="flex items-start space-x-3">
                  <span className="font-bold text-indigo-600">Parent:</span>
                  <span>TANTRA GROUP OF INDUSTRIES</span>
                </div>
                <div className="flex items-start space-x-3">
                  <span className="font-bold text-indigo-600">Location:</span>
                  <span>Corporate Towers, India</span>
                </div>
                <div className="flex items-start space-x-3">
                  <span className="font-bold text-indigo-600">Email:</span>
                  <span className="font-mono">corporate@tantragroup.com</span>
                </div>
              </div>
            </div>

            <div className="pt-6 mt-6 border-t border-gray-100">
              <span className="text-xs text-gray-400 font-medium">
                For corporate branding guidelines and official media kit, access the Corporate Leadership Portal.
              </span>
            </div>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}