'use client';

import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';

export interface BrandingData {
  id: number;
  logoUrl: string | null;
  faviconUrl: string | null;
  bannerUrl: string | null;
  primaryColor: string;
  secondaryColor: string;
}

const DEFAULT_BRANDING: BrandingData = {
  id: 1,
  logoUrl: null,
  faviconUrl: null,
  bannerUrl: null,
  primaryColor: '#4F46E5',
  secondaryColor: '#9333EA',
};

interface BrandingContextType {
  branding: BrandingData;
  loading: boolean;
  refreshBranding: () => Promise<void>;
}

const BrandingContext = createContext<BrandingContextType>({
  branding: DEFAULT_BRANDING,
  loading: true,
  refreshBranding: async () => {},
});

export function BrandingProvider({ children }: { children: ReactNode }) {
  const [branding, setBranding] = useState<BrandingData>(DEFAULT_BRANDING);
  const [loading, setLoading] = useState<boolean>(true);

  const fetchBranding = async () => {
    try {
      const res = await fetch('/api/branding');
      const data = await res.json();
      if (res.ok && data.branding) {
        setBranding(data.branding);
        
        // Update favicon dynamically if specified
        if (data.branding.faviconUrl) {
          let link: HTMLLinkElement | null = document.querySelector("link[rel*='icon']");
          if (!link) {
            link = document.createElement('link');
            link.rel = 'shortcut icon';
            document.getElementsByTagName('head')[0].appendChild(link);
          }
          link.href = data.branding.faviconUrl;
        }
      }
    } catch (err) {
      console.error('Error fetching global branding:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBranding();
  }, []);

  return (
    <BrandingContext.Provider value={{ branding, loading, refreshBranding: fetchBranding }}>
      {children}
    </BrandingContext.Provider>
  );
}

export function useBranding() {
  return useContext(BrandingContext);
}
