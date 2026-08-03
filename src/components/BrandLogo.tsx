'use client';

import React, { useState } from 'react';
import { useBranding } from './BrandingContext';

interface BrandLogoProps {
  logoUrl?: string | null;
  fallbackText?: string;
  className?: string;
  size?: 'sm' | 'md' | 'lg' | 'xl';
  showSubText?: boolean;
}

export function BrandLogo({
  logoUrl,
  fallbackText = 'TANTRA GROUP OF INDUSTRIES',
  className = '',
  size = 'md',
  showSubText = true,
}: BrandLogoProps) {
  const { branding } = useBranding();
  const [imageError, setImageError] = useState(false);

  const effectiveUrl = logoUrl !== undefined ? logoUrl : branding.logoUrl;

  const sizeClasses = {
    sm: { img: 'h-8 auto', text: 'text-sm font-bold', icon: 'w-7 h-7 text-xs' },
    md: { img: 'h-10 auto', text: 'text-base font-bold', icon: 'w-9 h-9 text-sm' },
    lg: { img: 'h-14 auto', text: 'text-xl font-bold', icon: 'w-12 h-12 text-lg' },
    xl: { img: 'h-20 auto', text: 'text-2xl font-bold', icon: 'w-16 h-16 text-xl' },
  };

  const currentSize = sizeClasses[size] || sizeClasses.md;

  // Render Image if URL exists and hasn't failed to load
  if (effectiveUrl && !imageError) {
    return (
      <div className={`inline-flex items-center space-x-3 ${className}`}>
        <img
          src={effectiveUrl}
          alt={fallbackText}
          onError={() => setImageError(true)}
          className={`object-contain max-h-full ${currentSize.img}`}
        />
      </div>
    );
  }

  // Fallback System: Styled brand icon badge + Text fallback
  const firstLetter = fallbackText ? fallbackText.charAt(0).toUpperCase() : 'T';
  const isParent = fallbackText.toUpperCase().includes('TANTRA GROUP');

  return (
    <div className={`inline-flex items-center space-x-3 ${className}`}>
      <div
        className={`${currentSize.icon} bg-gradient-to-br from-indigo-600 to-purple-600 text-white rounded-lg flex items-center justify-center font-black shadow-sm shrink-0`}
      >
        <span>{firstLetter}</span>
      </div>
      <div>
        <span className={`${currentSize.text} text-gray-900 tracking-tight block`}>
          {isParent ? (
            <>
              TANTRA GROUP
              {showSubText && (
                <span className="text-gray-500 font-normal text-xs block -mt-1 tracking-wider uppercase">
                  OF INDUSTRIES
                </span>
              )}
            </>
          ) : (
            fallbackText
          )}
        </span>
      </div>
    </div>
  );
}
