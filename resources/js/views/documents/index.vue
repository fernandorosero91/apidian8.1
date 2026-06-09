<template>
  <div class="card fade-in">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span><i class="fas fa-file-invoice mr-2"></i> Documentos</span>
      <span class="header-badge">{{ tableData.length }}</span>
    </div>
    <div class="card-body p-0">
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Cargando...</p>
      </div>
      
      <div v-else-if="tableData.length === 0" class="empty-state">
        <i class="fas fa-file-alt"></i>
        <h3>Sin documentos</h3>
        <p>Los documentos aparecerán aquí</p>
      </div>
      
      <div v-else class="table-responsive">
        <el-table :data="tableData" style="width: 100%">
          <el-table-column prop="key" label="#" width="60" align="center">
            <template slot-scope="scope">
              <span class="row-number">{{ scope.row.key }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="number" label="Número" width="100">
            <template slot-scope="scope">
              <strong>{{ scope.row.number }}</strong>
            </template>
          </el-table-column>
          <el-table-column prop="client" label="Cliente" min-width="160">
            <template slot-scope="scope">
              {{ scope.row.client || '-' }}
            </template>
          </el-table-column>
          <el-table-column prop="currency" label="Moneda" width="80" align="center">
            <template slot-scope="scope">
              <span class="currency-badge">{{ scope.row.currency }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="date" label="Fecha" width="100">
            <template slot-scope="scope">
              {{ scope.row.date }}
            </template>
          </el-table-column>
          <el-table-column prop="sale" label="Venta" width="110" align="right">
            <template slot-scope="scope">
              {{ formatNumber(scope.row.sale) }}
            </template>
          </el-table-column>
          <el-table-column prop="total_discount" label="Descuento" width="110" align="right">
            <template slot-scope="scope">
              <span class="text-warning">{{ formatNumber(scope.row.total_discount) }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="total_tax" label="Impuesto" width="110" align="right">
            <template slot-scope="scope">
              {{ formatNumber(scope.row.total_tax) }}
            </template>
          </el-table-column>
          <el-table-column prop="total" label="Total" width="120" align="right">
            <template slot-scope="scope">
              <strong class="text-success">{{ formatNumber(scope.row.total) }}</strong>
            </template>
          </el-table-column>
          <el-table-column fixed="right" label="Descargar" width="110" align="center">
            <template slot-scope="scope">
              <div class="action-btns">
                <a :href="`${resource}/downloadxml/${scope.row.xml}`" target="_blank" class="btn-action btn-xml" title="XML">
                  <i class="fas fa-code"></i>
                </a>
                <a :href="`${resource}/downloadpdf/${scope.row.pdf}`" target="_blank" class="btn-action btn-pdf" title="PDF">
                  <i class="fas fa-file-pdf"></i>
                </a>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.header-badge {
  background: rgba(255,255,255,0.15);
  color: #fff;
  padding: 2px 10px;
  border-radius: 4px;
  font-size: 12px;
}

.loading-state {
  text-align: center;
  padding: 50px 20px;
}

.loading-state p {
  color: #64748b;
  font-size: 13px;
  margin-top: 12px;
}

.loading-spinner {
  display: inline-block;
  width: 24px;
  height: 24px;
  border: 2px solid #e2e8f0;
  border-radius: 50%;
  border-top-color: #2563eb;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.row-number {
  background: #f1f5f9;
  color: #64748b;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
}

.currency-badge {
  background: #f1f5f9;
  color: #475569;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 11px;
}

.text-warning {
  color: #d97706;
}

.text-success {
  color: #059669;
}

.action-btns {
  display: flex;
  gap: 4px;
  justify-content: center;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 4px;
  color: #fff;
  font-size: 12px;
  text-decoration: none;
  transition: opacity 0.15s;
}

.btn-action:hover {
  opacity: 0.85;
}

.btn-xml {
  background: #0284c7;
}

.btn-pdf {
  background: #dc2626;
}
</style>

<script>
export default {
  data() {
    return {
      resource: "documents",
      tableData: [],
      loading: true
    };
  },
  created() {
    this.getRecords();
  },
  methods: {
    formatNumber(value) {
      if (!value && value !== 0) return '-';
      return new Intl.NumberFormat('es-CO').format(value);
    },
    getRecords() {
      this.loading = true;
      this.$http.get(`/${this.resource}/records`)
        .then(response => {
          this.tableData = response.data.data;
        })
        .catch(() => {
          this.$message.error('Error al cargar');
        })
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>
