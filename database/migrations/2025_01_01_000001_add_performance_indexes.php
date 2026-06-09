<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para agregar índices de rendimiento
 * 
 * Esta migración agrega índices compuestos para optimizar las consultas
 * más frecuentes en la API sin modificar la estructura existente.
 * 
 * SEGURO: Solo agrega índices, no modifica datos ni estructura de tablas.
 */
class AddPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Índices para tabla documents - consultas frecuentes
        Schema::table('documents', function (Blueprint $table) {
            // Índice para búsqueda por empresa + tipo + estado
            if (!$this->indexExists('documents', 'idx_docs_company_type_state')) {
                $table->index(
                    ['identification_number', 'type_document_id', 'state_document_id'],
                    'idx_docs_company_type_state'
                );
            }

            // Índice para búsqueda por fecha de emisión
            if (!$this->indexExists('documents', 'idx_docs_date_issue')) {
                $table->index('date_issue', 'idx_docs_date_issue');
            }

            // Índice para búsqueda por cliente
            if (!$this->indexExists('documents', 'idx_docs_customer')) {
                $table->index('customer', 'idx_docs_customer');
            }
        });

        // Índices para tabla document_payrolls
        Schema::table('document_payrolls', function (Blueprint $table) {
            if (!$this->indexExists('document_payrolls', 'idx_payroll_company_type_state')) {
                $table->index(
                    ['identification_number', 'type_document_id', 'state_document_id'],
                    'idx_payroll_company_type_state'
                );
            }

            if (!$this->indexExists('document_payrolls', 'idx_payroll_employee')) {
                $table->index('employee_id', 'idx_payroll_employee');
            }
        });

        // Índices para tabla received_documents
        Schema::table('received_documents', function (Blueprint $table) {
            if (!$this->indexExists('received_documents', 'idx_received_customer_state')) {
                $table->index(
                    ['customer', 'state_document_id'],
                    'idx_received_customer_state'
                );
            }
        });

        // Índice para tabla logs - limpieza y consultas
        Schema::table('logs', function (Blueprint $table) {
            if (!$this->indexExists('logs', 'idx_logs_created_at')) {
                $table->index('created_at', 'idx_logs_created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_docs_company_type_state');
            $table->dropIndex('idx_docs_date_issue');
            $table->dropIndex('idx_docs_customer');
        });

        Schema::table('document_payrolls', function (Blueprint $table) {
            $table->dropIndex('idx_payroll_company_type_state');
            $table->dropIndex('idx_payroll_employee');
        });

        Schema::table('received_documents', function (Blueprint $table) {
            $table->dropIndex('idx_received_customer_state');
        });

        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex('idx_logs_created_at');
        });
    }

    /**
     * Verificar si un índice ya existe
     */
    private function indexExists($table, $indexName): bool
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);
        
        return array_key_exists($indexName, $indexes);
    }
}
