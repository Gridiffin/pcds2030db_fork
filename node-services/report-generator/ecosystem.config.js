module.exports = {
  apps: [{
    name: 'pcds2030-report-generator',
    script: 'server.js',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '256M',
    env: {
      NODE_ENV: 'development',
      PORT: 3000,
      HOST: '0.0.0.0'
    },
    env_production: {
      NODE_ENV: 'production',
      PORT: 3000,
      HOST: '0.0.0.0'
    }
  }]
};