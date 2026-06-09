<template>
  <div class="card fade-in">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span><i class="fas fa-building mr-2"></i> Lista de Empresas</span>
      <span class="header-badge">{{ tableData.length }}</span>
    </div>
    <div class="card-body p-0">
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Cargando...</p>
      </div>
      
      <div v-else-if="tableData.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>Sin empresas</h3>
        <p>Las empresas aparecerán aquí cuando se registren</p>
      </div>
      
      <div v-else class="table-responsive">
        <el-table :data="tableData" style="width: 100%">
          <el-table-column prop="key" label="#" width="60" align="center">
            <template slot-scope="scope">
              <span class="row-number">{{ scope.row.key }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="identification_number" label="NIT" width="140">
            <template slot-scope="scope">
              <code class="nit-code">{{ scope.row.identification_number }}</code>
            </template>
          </el-table-column>
          <el-table-column prop="name" label="Empresa" min-width="180">
            <template slot-scope="scope">
              <div class="company-cell">
                <div class="company-avatar">{{ getInitials(scope.row.name) }}</div>
                <span>{{ scope.row.name }}</span>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="email" label="Correo" min-width="200">
            <template slot-scope="scope">
              <span class="text-muted">{{ scope.row.email }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="created_at" label="Fecha" width="120">
            <template slot-scope="scope">
              {{ formatDate(scope.row.created_at) }}
            </template>
          </el-table-column>
          <el-table-column prop="token" label="Token API" width="240">
            <template slot-scope="scope">
              <div class="token-cell">
                <code>{{ truncateToken(scope.row.token) }}</code>
                <button class="copy-btn" @click="copyToken(scope.row.token)" title="Copiar">
                  <i class="fas fa-copy"></i>
                </button>
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

.company-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.company-avatar {
  width: 32px;
  height: 32px;
  background: #1a2332;
  color: #fff;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 600;
}

.nit-code {
  background: #f1f5f9;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: #475569;
}

.text-muted {
  color: #64748b;
}

.token-cell {
  display: flex;
  align-items: center;
  gap: 6px;
}

.token-cell code {
  background: #f1f5f9;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  color: #475569;
}

.copy-btn {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  transition: color 0.15s;
}

.copy-btn:hover {
  color: #2563eb;
}
</style>

<script>
export default {
  data() {
    return {
      resource: "configuration",
      tableData: [],
      loading: true
    };
  },
  created() {
    this.getRecords();
  },
  methods: {
    getInitials(name) {
      if (!name) return '?';
      return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    },
    formatDate(date) {
      if (!date) return '-';
      return new Date(date).toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' });
    },
    truncateToken(token) {
      if (!token) return '-';
      return token.substring(0, 18) + '...';
    },
    copyToken(token) {
      navigator.clipboard.writeText(token);
      this.$message.success('Token copiado');
    },
    getFilterRecord(array) {
      return array.filter(x => x.identification_number != null);
    },
    getRecords() {
      this.loading = true;
      this.$http.get(`/${this.resource}/records`)
        .then(response => {
          this.tableData = this.getFilterRecord(response.data.data);
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
