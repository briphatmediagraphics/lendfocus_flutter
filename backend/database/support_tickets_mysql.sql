CREATE TABLE IF NOT EXISTS support_tickets (
    id CHAR(36) NOT NULL,
    ticket_number VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    project_id CHAR(36) NULL,
    customer_name VARCHAR(255) NULL,
    customer_email VARCHAR(255) NULL,
    customer_phone VARCHAR(30) NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open', 'pending', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    assigned_to BIGINT UNSIGNED NULL,
    last_replied_at TIMESTAMP NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_support_tickets_ticket_number (ticket_number),
    KEY idx_support_tickets_user_id (user_id),
    KEY idx_support_tickets_status (status),
    KEY idx_support_tickets_priority (priority),
    KEY idx_support_tickets_project_id (project_id),
    KEY idx_support_tickets_assigned_to (assigned_to),
    CONSTRAINT fk_support_tickets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_support_tickets_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE support_tickets
    ADD COLUMN IF NOT EXISTS customer_name VARCHAR(255) NULL AFTER user_id,
    ADD COLUMN IF NOT EXISTS customer_email VARCHAR(255) NULL AFTER customer_name,
    ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(30) NULL AFTER customer_email,
    ADD COLUMN IF NOT EXISTS admin_notes TEXT NULL AFTER last_replied_at;

CREATE TABLE IF NOT EXISTS support_ticket_replies (
    id CHAR(36) NOT NULL,
    support_ticket_id CHAR(36) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    sender_type ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    message TEXT NOT NULL,
    is_internal BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_support_ticket_replies_ticket_id (support_ticket_id),
    KEY idx_support_ticket_replies_user_id (user_id),
    CONSTRAINT fk_support_ticket_replies_ticket FOREIGN KEY (support_ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_support_ticket_replies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE system_notifications
    ADD COLUMN IF NOT EXISTS channel VARCHAR(32) NOT NULL DEFAULT 'in_app' AFTER body,
    ADD COLUMN IF NOT EXISTS recipients JSON NULL AFTER channel,
    ADD COLUMN IF NOT EXISTS sent_at TIMESTAMP NULL AFTER recipients;
