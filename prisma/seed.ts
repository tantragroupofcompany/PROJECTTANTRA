import { PrismaClient } from '@prisma/client';
import { PrismaPg } from '@prisma/adapter-pg';
import bcrypt from 'bcryptjs';

const connectionString = process.env.DATABASE_URL || 'postgres://postgres:postgres@localhost:51214/template1?sslmode=disable';
const adapter = new PrismaPg({ connectionString });
const prisma = new PrismaClient({ adapter });

async function main() {
  // Create founder user
  const founderEmail = 'jadavnileshbhai2006@gmail.com';
  const existingUser = await prisma.user.findUnique({ where: { email: founderEmail } });

  if (!existingUser) {
    const passwordHash = await bcrypt.hash('Tantra@2026#Founder', 12);
    await prisma.user.create({
      data: {
        username: 'founder',
        email: founderEmail,
        passwordHash,
        role: 'Founder',
        status: 'Active',
      },
    });
    console.log('✓ Created founder user');
  } else {
    console.log('Founder user already exists');
  }

  // Create leadership profiles
  const profiles = [
    {
      name: 'JADAV NILESH',
      designation: 'Founder',
      biography: 'Founder of TANTRA GROUP OF INDUSTRIES, leading the vision and strategic direction of the corporate ecosystem.',
      status: 'Active',
      displayOrder: 1,
    },
    {
      name: 'JADAV JAYESH',
      designation: 'CEO & Managing Director',
      biography: 'CEO & Managing Director of TANTRA GROUP OF INDUSTRIES, driving operational excellence and corporate growth.',
      status: 'Active',
      displayOrder: 2,
    },
    {
      name: 'JADAV NAYNA',
      designation: 'Chairman',
      biography: 'Chairman of TANTRA GROUP OF INDUSTRIES, providing governance and strategic oversight across all companies.',
      status: 'Active',
      displayOrder: 3,
    },
  ];

  for (const profile of profiles) {
    const existing = await prisma.leadershipProfile.findFirst({
      where: { name: profile.name },
    });
    if (!existing) {
      await prisma.leadershipProfile.create({ data: profile });
      console.log(`✓ Created leadership profile: ${profile.name}`);
    }
  }

  // Create branding settings if not exists
  const branding = await prisma.brandingSettings.findFirst();
  if (!branding) {
    await prisma.brandingSettings.create({
      data: {
        primaryColor: '#4F46E5',
        secondaryColor: '#9333EA',
      },
    });
    console.log('✓ Created branding settings');
  }

  console.log('\nSeed completed successfully.');
  console.log('Founder Login: jadavnileshbhai2006@gmail.com (OTP via email)');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(() => prisma.$disconnect());