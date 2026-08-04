const { PrismaClient } = require('@prisma/client');
const { PrismaLibSql } = require('@prisma/adapter-libsql');

(async () => {
  const prisma = new PrismaClient({
    adapter: new PrismaLibSql({ url: 'file:./prisma/dev.db' })
  });

  const users = await prisma.user.findMany();
  console.log('Users count:', users.length);
  console.log(JSON.stringify(users, null, 2));

  await prisma.$disconnect();
})();