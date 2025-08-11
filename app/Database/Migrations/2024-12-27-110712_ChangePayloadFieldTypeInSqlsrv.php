<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Queue.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Queue\Database\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;

/**
 * @property BaseConnection $db
 */
class ChangePayloadFieldTypeInSqlsrv extends Migration
{
    public function up(): void
    {
        if ($this->db->DBDriver === 'SQLSRV') {
            $fields = [
                'payload' => [
                    'name'       => 'payload',
                    'type'       => 'NVARCHAR',
                    'constraint' => 'MAX',
                    'null'       => false,
                ],
            ];
            $this->forge->modifyColumn('queue_jobs', $fields);
        }
    }

    public function down(): void
    {
        if ($this->db->DBDriver === 'SQLSRV') {
            $fields = [
                'payload' => [
                    'name' => 'payload',
                    'type' => 'TEXT', // already deprecated
                    'null' => false,
                ],
            ];
            $this->forge->modifyColumn('queue_jobs', $fields);
        }
    }
}
