# Déploiement sur Railway

Le dépôt contient tout ce qu'il faut :

- `Dockerfile` : build Vite (Node 24), puis PHP 8.4 + FrankenPHP qui sert `web/` sur `$PORT`.
- `docker/Caddyfile`, `docker/php.ini` : serveur web et réglages PHP.
- `railway.json` : build par Dockerfile, `php craft up` avant chaque déploiement, health-check sur `/actions/app/health-check`.

## Mise en place (une seule fois)

1. **New Project → Deploy from GitHub repo**, branche `main`.
2. **+ New → Database → MySQL** dans le même projet.
3. Service web → **Settings → Volumes** : ajouter un volume monté sur `/app/web/uploads`.
   Sans lui, les images téléversées disparaissent à chaque déploiement.
4. Service web → **Settings → Networking → Generate Domain**.
5. Service web → **Variables** :

   ```
   CRAFT_APP_ID=hypotheca
   CRAFT_ENVIRONMENT=production
   CRAFT_SECURITY_KEY=<la même que dans le .env local>
   CRAFT_DEV_MODE=false
   CRAFT_ALLOW_ADMIN_CHANGES=false
   CRAFT_DISALLOW_ROBOTS=true
   PRIMARY_SITE_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}

   CRAFT_DB_DRIVER=mysql
   CRAFT_DB_SERVER=${{MySQL.MYSQLHOST}}
   CRAFT_DB_PORT=${{MySQL.MYSQLPORT}}
   CRAFT_DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   CRAFT_DB_USER=${{MySQL.MYSQLUSER}}
   CRAFT_DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

   MAILER_DSN=resend+api://<clé API Resend>@default
   MAIL_FROM_EMAIL=<adresse sur un domaine vérifié dans Resend>
   MAIL_FROM_NAME=Hypotheca
   ```

   Passer `CRAFT_DISALLOW_ROBOTS=false` au lancement public.

## Importer la base locale

Le premier déploiement échouera tant que la base est vide : c'est normal.

```bash
ddev export-db --file=hypotheca.sql.gz
```

Dans Railway, service MySQL → **Variables**, copier `MYSQL_PUBLIC_URL`, puis :

```bash
gunzip -c hypotheca.sql.gz | mysql -h <hôte> -P <port> -u root -p railway
```

Relancer ensuite le déploiement (**Deployments → Redeploy**).

## Images

Le volume démarre vide. Téléverser de nouveau les images depuis le panneau d'admin (Ressources).

## Au quotidien

- Les sections et les champs ne se modifient pas en production (`CRAFT_ALLOW_ADMIN_CHANGES=false`).
  On les change sous DDEV, on commite `config/project/`, et `craft up` les applique au déploiement.
- Le contenu (entrées, images) se gère directement en production.

## Tester l'image en local

```bash
docker build -t hypotheca-railway .
docker run --rm -p 8099:8080 --env-file .env \
  -e CRAFT_DB_DRIVER=mysql -e CRAFT_DB_SERVER=host.docker.internal \
  -e CRAFT_DB_PORT=$(ddev describe -j | jq -r .raw.dbinfo.published_port) \
  -e CRAFT_DB_DATABASE=db -e CRAFT_DB_USER=db -e CRAFT_DB_PASSWORD=db \
  -e CRAFT_ENVIRONMENT=production -e PRIMARY_SITE_URL=http://localhost:8099 \
  hypotheca-railway
```
