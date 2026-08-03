-- CreateTable
CREATE TABLE "users" (
    "id" SERIAL NOT NULL,
    "username" VARCHAR(50) NOT NULL,
    "email" VARCHAR(255) NOT NULL,
    "password_hash" VARCHAR(255),
    "role" VARCHAR(50) NOT NULL DEFAULT 'Employee',
    "status" VARCHAR(20) NOT NULL DEFAULT 'Active',
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "users_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "companies" (
    "id" SERIAL NOT NULL,
    "company_name" VARCHAR(200) NOT NULL,
    "company_code" VARCHAR(20) NOT NULL,
    "company_description" TEXT,
    "company_logo" VARCHAR(500),
    "company_banner" VARCHAR(500),
    "company_favicon" VARCHAR(500),
    "website_url" VARCHAR(500),
    "status" VARCHAR(20) NOT NULL DEFAULT 'Draft',
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "companies_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "leadership_profiles" (
    "id" SERIAL NOT NULL,
    "name" VARCHAR(100) NOT NULL,
    "designation" VARCHAR(100) NOT NULL,
    "biography" TEXT,
    "photo_url" VARCHAR(500),
    "status" VARCHAR(20) NOT NULL DEFAULT 'Active',
    "display_order" INTEGER NOT NULL DEFAULT 0,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "leadership_profiles_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "otp_verifications" (
    "id" SERIAL NOT NULL,
    "email" VARCHAR(255) NOT NULL,
    "otp" VARCHAR(10) NOT NULL,
    "expires_at" TIMESTAMP(3) NOT NULL,
    "used" BOOLEAN NOT NULL DEFAULT false,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "otp_verifications_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "media_library" (
    "id" SERIAL NOT NULL,
    "file_name" VARCHAR(255) NOT NULL,
    "file_url" VARCHAR(500) NOT NULL,
    "file_type" VARCHAR(50) NOT NULL,
    "company_id" INTEGER,
    "uploaded_by" INTEGER,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "media_library_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "branding_settings" (
    "id" SERIAL NOT NULL,
    "logo_url" VARCHAR(500),
    "favicon_url" VARCHAR(500),
    "banner_url" VARCHAR(500),
    "primary_color" VARCHAR(50),
    "secondary_color" VARCHAR(50),
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "branding_settings_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "audit_logs" (
    "id" SERIAL NOT NULL,
    "user_id" INTEGER NOT NULL,
    "action" VARCHAR(100) NOT NULL,
    "module" VARCHAR(50) NOT NULL,
    "description" TEXT,
    "ip_address" VARCHAR(45),
    "user_agent" VARCHAR(500),
    "timestamp" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "audit_logs_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "sessions" (
    "id" VARCHAR(128) NOT NULL,
    "user_id" INTEGER NOT NULL,
    "ip_address" VARCHAR(45) NOT NULL,
    "user_agent" VARCHAR(500),
    "payload" TEXT,
    "last_activity" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "sessions_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "websites" (
    "id" SERIAL NOT NULL,
    "company_id" INTEGER NOT NULL,
    "template" VARCHAR(50) NOT NULL DEFAULT 'corporate',
    "status" VARCHAR(20) NOT NULL DEFAULT 'Draft',
    "theme_settings" JSONB,
    "navigation" JSONB,
    "domain" VARCHAR(255),
    "subdomain" VARCHAR(100),
    "analytics" JSONB,
    "published_at" TIMESTAMP(3),
    "archived_at" TIMESTAMP(3),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "websites_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "pages" (
    "id" SERIAL NOT NULL,
    "website_id" INTEGER NOT NULL,
    "company_id" INTEGER NOT NULL,
    "page_name" VARCHAR(100) NOT NULL,
    "page_slug" VARCHAR(100) NOT NULL,
    "page_content" TEXT,
    "page_status" VARCHAR(20) NOT NULL DEFAULT 'Draft',
    "meta_title" VARCHAR(255),
    "meta_description" TEXT,
    "sort_order" INTEGER NOT NULL DEFAULT 0,
    "is_required" BOOLEAN NOT NULL DEFAULT false,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "pages_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "departments" (
    "id" SERIAL NOT NULL,
    "company_id" INTEGER NOT NULL,
    "department_name" VARCHAR(100) NOT NULL,
    "department_code" VARCHAR(20) NOT NULL,
    "description" TEXT,
    "headId" INTEGER,
    "status" VARCHAR(20) NOT NULL DEFAULT 'Active',
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "departments_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "employees" (
    "id" SERIAL NOT NULL,
    "employee_id" VARCHAR(50) NOT NULL,
    "company_id" INTEGER NOT NULL,
    "department_id" INTEGER,
    "full_name" VARCHAR(100) NOT NULL,
    "email" VARCHAR(255) NOT NULL,
    "phone" VARCHAR(20),
    "designation" VARCHAR(100) NOT NULL,
    "manager_id" INTEGER,
    "role" VARCHAR(50) NOT NULL DEFAULT 'Employee',
    "status" VARCHAR(20) NOT NULL DEFAULT 'Active',
    "joining_date" TIMESTAMP(3),
    "avatar" VARCHAR(500),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "employees_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "approvals" (
    "id" SERIAL NOT NULL,
    "company_id" INTEGER,
    "employee_id" INTEGER,
    "approval_type" VARCHAR(50) NOT NULL,
    "status" VARCHAR(20) NOT NULL DEFAULT 'Pending',
    "requested_by" INTEGER NOT NULL,
    "reviewed_by" INTEGER,
    "review_notes" TEXT,
    "reviewed_at" TIMESTAMP(3),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "approvals_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "notifications" (
    "id" SERIAL NOT NULL,
    "user_id" INTEGER,
    "company_id" INTEGER,
    "type" VARCHAR(50) NOT NULL,
    "title" VARCHAR(255) NOT NULL,
    "message" TEXT NOT NULL,
    "channel" VARCHAR(50) NOT NULL DEFAULT 'Dashboard',
    "is_read" BOOLEAN NOT NULL DEFAULT false,
    "read_at" TIMESTAMP(3),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "notifications_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "reports" (
    "id" SERIAL NOT NULL,
    "company_id" INTEGER,
    "report_type" VARCHAR(50) NOT NULL,
    "report_name" VARCHAR(255) NOT NULL,
    "report_format" VARCHAR(20) NOT NULL DEFAULT 'PDF',
    "filters" JSONB,
    "generated_by" INTEGER NOT NULL,
    "file_path" VARCHAR(500),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "reports_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "assets" (
    "id" SERIAL NOT NULL,
    "company_id" INTEGER NOT NULL,
    "asset_name" VARCHAR(200) NOT NULL,
    "asset_type" VARCHAR(50) NOT NULL,
    "serial_number" VARCHAR(100),
    "value" DECIMAL(15,2) NOT NULL DEFAULT 0,
    "assigned_to" INTEGER,
    "status" VARCHAR(20) NOT NULL DEFAULT 'Available',
    "purchase_date" TIMESTAMP(3),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "assets_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "documents" (
    "id" SERIAL NOT NULL,
    "company_id" INTEGER,
    "document_name" VARCHAR(255) NOT NULL,
    "document_type" VARCHAR(50) NOT NULL,
    "file_url" VARCHAR(500),
    "description" TEXT,
    "expiry_date" TIMESTAMP(3),
    "created_by" INTEGER NOT NULL,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "documents_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "compliance_records" (
    "id" SERIAL NOT NULL,
    "company_id" INTEGER NOT NULL,
    "compliance_type" VARCHAR(50) NOT NULL,
    "title" VARCHAR(255) NOT NULL,
    "issuing_authority" VARCHAR(255),
    "issue_date" TIMESTAMP(3),
    "expiry_date" TIMESTAMP(3) NOT NULL,
    "status" VARCHAR(20) NOT NULL DEFAULT 'Active',
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "compliance_records_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "ai_insights" (
    "id" SERIAL NOT NULL,
    "company_id" INTEGER,
    "insight_type" VARCHAR(50) NOT NULL,
    "title" VARCHAR(255) NOT NULL,
    "description" TEXT NOT NULL,
    "confidence_score" DECIMAL(5,2) NOT NULL DEFAULT 0,
    "is_read" BOOLEAN NOT NULL DEFAULT false,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "ai_insights_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "search_index" (
    "id" SERIAL NOT NULL,
    "entity_type" VARCHAR(50) NOT NULL,
    "entity_id" INTEGER NOT NULL,
    "title" VARCHAR(255) NOT NULL,
    "description" TEXT,
    "keywords" TEXT,
    "url" VARCHAR(500),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "search_index_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "projects" (
    "id" SERIAL NOT NULL,
    "company_id" INTEGER NOT NULL,
    "project_name" VARCHAR(200) NOT NULL,
    "description" TEXT,
    "status" VARCHAR(20) NOT NULL DEFAULT 'Planning',
    "start_date" TIMESTAMP(3),
    "end_date" TIMESTAMP(3),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "projects_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "tasks" (
    "id" SERIAL NOT NULL,
    "project_id" INTEGER,
    "company_id" INTEGER NOT NULL,
    "task_name" VARCHAR(200) NOT NULL,
    "assigned_to" INTEGER,
    "status" VARCHAR(20) NOT NULL DEFAULT 'Pending',
    "priority" VARCHAR(20) NOT NULL DEFAULT 'Medium',
    "due_date" TIMESTAMP(3),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "tasks_pkey" PRIMARY KEY ("id")
);

-- CreateIndex
CREATE UNIQUE INDEX "users_username_key" ON "users"("username");

-- CreateIndex
CREATE UNIQUE INDEX "users_email_key" ON "users"("email");

-- CreateIndex
CREATE UNIQUE INDEX "companies_company_code_key" ON "companies"("company_code");

-- CreateIndex
CREATE INDEX "idx_otp_email" ON "otp_verifications"("email");

-- CreateIndex
CREATE INDEX "idx_media_company" ON "media_library"("company_id");

-- CreateIndex
CREATE INDEX "idx_media_file_type" ON "media_library"("file_type");

-- CreateIndex
CREATE INDEX "idx_audit_user" ON "audit_logs"("user_id");

-- CreateIndex
CREATE INDEX "idx_audit_module" ON "audit_logs"("module");

-- CreateIndex
CREATE INDEX "idx_audit_timestamp" ON "audit_logs"("timestamp");

-- CreateIndex
CREATE INDEX "idx_sessions_user" ON "sessions"("user_id");

-- CreateIndex
CREATE INDEX "idx_sessions_last_activity" ON "sessions"("last_activity");

-- CreateIndex
CREATE INDEX "idx_websites_company" ON "websites"("company_id");

-- CreateIndex
CREATE INDEX "idx_websites_status" ON "websites"("status");

-- CreateIndex
CREATE INDEX "idx_pages_website" ON "pages"("website_id");

-- CreateIndex
CREATE INDEX "idx_pages_company" ON "pages"("company_id");

-- CreateIndex
CREATE INDEX "idx_dept_company" ON "departments"("company_id");

-- CreateIndex
CREATE UNIQUE INDEX "employees_employee_id_key" ON "employees"("employee_id");

-- CreateIndex
CREATE INDEX "idx_emp_company" ON "employees"("company_id");

-- CreateIndex
CREATE INDEX "idx_emp_department" ON "employees"("department_id");

-- CreateIndex
CREATE INDEX "idx_approval_company" ON "approvals"("company_id");

-- CreateIndex
CREATE INDEX "idx_approval_status" ON "approvals"("status");

-- CreateIndex
CREATE INDEX "idx_notif_user" ON "notifications"("user_id");

-- CreateIndex
CREATE INDEX "idx_notif_read" ON "notifications"("is_read");

-- CreateIndex
CREATE INDEX "idx_report_company" ON "reports"("company_id");

-- CreateIndex
CREATE INDEX "idx_asset_company" ON "assets"("company_id");

-- CreateIndex
CREATE INDEX "idx_doc_company" ON "documents"("company_id");

-- CreateIndex
CREATE INDEX "idx_compliance_company" ON "compliance_records"("company_id");

-- CreateIndex
CREATE INDEX "idx_ai_company" ON "ai_insights"("company_id");

-- CreateIndex
CREATE INDEX "idx_search_type" ON "search_index"("entity_type");

-- CreateIndex
CREATE INDEX "idx_search_title" ON "search_index"("title");

-- CreateIndex
CREATE INDEX "idx_project_company" ON "projects"("company_id");

-- CreateIndex
CREATE INDEX "idx_task_project" ON "tasks"("project_id");

-- AddForeignKey
ALTER TABLE "media_library" ADD CONSTRAINT "media_library_company_id_fkey" FOREIGN KEY ("company_id") REFERENCES "companies"("id") ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "media_library" ADD CONSTRAINT "media_library_uploaded_by_fkey" FOREIGN KEY ("uploaded_by") REFERENCES "users"("id") ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "audit_logs" ADD CONSTRAINT "audit_logs_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "sessions" ADD CONSTRAINT "sessions_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "websites" ADD CONSTRAINT "websites_company_id_fkey" FOREIGN KEY ("company_id") REFERENCES "companies"("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "pages" ADD CONSTRAINT "pages_website_id_fkey" FOREIGN KEY ("website_id") REFERENCES "websites"("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "departments" ADD CONSTRAINT "departments_company_id_fkey" FOREIGN KEY ("company_id") REFERENCES "companies"("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "employees" ADD CONSTRAINT "employees_company_id_fkey" FOREIGN KEY ("company_id") REFERENCES "companies"("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "employees" ADD CONSTRAINT "employees_department_id_fkey" FOREIGN KEY ("department_id") REFERENCES "departments"("id") ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "assets" ADD CONSTRAINT "assets_company_id_fkey" FOREIGN KEY ("company_id") REFERENCES "companies"("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "compliance_records" ADD CONSTRAINT "compliance_records_company_id_fkey" FOREIGN KEY ("company_id") REFERENCES "companies"("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "projects" ADD CONSTRAINT "projects_company_id_fkey" FOREIGN KEY ("company_id") REFERENCES "companies"("id") ON DELETE CASCADE ON UPDATE CASCADE;
