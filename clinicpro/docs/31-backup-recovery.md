# 31 — Backup & Recovery

## What to Backup

| Component | Location | Frequency |
|-----------|----------|-----------|
| Database | MySQL dump | Daily |
| Uploads | public/uploads/ | Weekly |
| Configuration | .env | On change |
| Application | clinicpro/ (full) | Monthly |
| Logs | storage/logs/ | Weekly |

## Backup Schedule

| Type | Frequency | Retention |
|------|-----------|-----------|
| Full DB dump | Daily | 30 days |
| Incremental | Hourly (if possible) | 7 days |
| Full site backup | Weekly | 4 weeks |
| Off-site backup | Monthly | 12 months |

## Database Backup

### Manual (CLI)
```bash
mysqldump -u root -p clinicpro > backup_$(date +%Y%m%d).sql
```

### Hostinger Automated
```
hPanel → Files → Backups → Enable automatic backups
```

### Restore
```bash
mysql -u root -p clinicpro < backup_20240101.sql
```

## File Backup

### Uploads Only
```bash
tar -czf uploads_$(date +%Y%m%d).tar.gz public/uploads/
```

### Full Application
```bash
tar -czf clinicpro_$(date +%Y%m%d).tar.gz clinicpro/
```

## Recovery Procedures

### Database Recovery
1. Stop application access (maintenance mode)
2. Drop and recreate database
3. Import latest backup
4. Verify data integrity
5. Resume access

### File Recovery
1. Extract backup archive
2. Restore to correct location
3. Set permissions
4. Verify uploads accessible

### Full System Recovery
1. Fresh server setup (PHP, MySQL, Apache)
2. Upload application files
3. Restore database from backup
4. Restore uploads from backup
5. Configure .env
6. Verify SSL
7. Test all modules

## Hostinger Backup Features

- Daily automatic backups (Premium plans)
- One-click restore
- Download backups locally
- 7 restoration points available
