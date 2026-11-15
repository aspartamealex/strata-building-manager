/** @type {import('next').NextConfig} */
const nextConfig = {
  eslint: {
    ignoreDuringBuilds: true,
  },
  typescript: {
    ignoreBuildErrors: true,
  },
  // The webpack cache under .next/cache is hundreds of megabytes. vercel-php
  // bundles the project directory into every PHP function, and that cache is
  // what pushes each function past Vercel's 250 MB limit.
  webpack: (config, { dev }) =>
  {
    if (!dev)
    {
      config.cache = false;
    }
    return config;
  },
}

module.exports = nextConfig 