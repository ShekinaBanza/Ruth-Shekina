# Application Mariage — Shekina & Ruth

Application web pour gérer les invités, les tables (25 tables x 10 places = 250
invités, nommées d'après les 25 premiers noms de votre liste de saints) et
générer automatiquement les invitations PDF personnalisées, fidèles à votre
maquette.

## Contenu du dossier

```
index.html          → page publique d'enregistrement (à donner à vos invités)
admin.html           → page administrateur (protégée par mot de passe)
config.php           → réglages (mot de passe admin, texte du mariage, tables)
api/                 → le "moteur" de l'application (PHP)
assets/              → branches florales utilisées dans le PDF
lib/                 → librairie PDF (FPDF) + génération de l'invitation
data/                → base de données (créée automatiquement au 1er lancement)
```

## 1. Pré-requis pour l'hébergement

Un hébergement PHP classique (mutualisé ou VPS) avec :
- PHP 7.4 ou plus récent (idéalement PHP 8.x)
- Extensions PHP activées : `pdo_sqlite`, `gd`, `zip`, `mbstring` (presque
  toujours déjà activées chez les hébergeurs standards : o2switch, Hostinger,
  Planethoster, Infomaniak, etc. — même les hébergements les moins chers les
  incluent en général)
- Pas besoin de MySQL : tout est stocké dans un simple fichier SQLite
  (`data/mariage.db`), créé automatiquement.

## 2. Installation (5 minutes)

1. Modifiez le mot de passe administrateur dans `config.php` :
   ```php
   define('ADMIN_PASSWORD', 'VotreNouveauMotDePasse');
   ```
2. Envoyez **tout le dossier** sur votre hébergement (via FTP, ou le
   gestionnaire de fichiers de votre panneau d'hébergement), par exemple dans
   `public_html/mariage/`.
3. Assurez-vous que le dossier `data/` est **accessible en écriture** par PHP
   (chmod 755 ou 775 si nécessaire — la plupart des hébergeurs le permettent
   par défaut).
4. C'est tout : ouvrez `https://votre-domaine.com/mariage/` dans un
   navigateur.

- Page invités : `https://votre-domaine.com/mariage/index.html`
- Page admin : `https://votre-domaine.com/mariage/admin.html`

## 3. Utilisation côté invité (`index.html`)

1. L'invité choisit **d'abord sa table** dans la liste déroulante (les tables
   complètes ou n'ayant plus assez de places sont automatiquement grisées).
2. Il indique s'il est Monsieur / Madame / Couple.
3. Il saisit son nom (ou les deux noms pour un couple).
4. Un clic sur "Enregistrer" → l'invitation PDF est générée immédiatement,
   avec son nom et le numéro + nom de sa table déjà remplis.
5. Boutons disponibles ensuite :
   - **Télécharger mon invitation (PDF)**
   - **Envoyer sur WhatsApp** — voir la remarque importante ci-dessous.

## 4. Utilisation côté administrateur (`admin.html`)

- Connexion avec le mot de passe défini dans `config.php`.
- Liste complète des invités, triable/filtrable par table ou par nom.
- Case à cocher **Présent** pour chaque invité (mise à jour instantanée).
- **🖨️ Imprimer la liste** → génère une version imprimable propre, groupée
  par table, avec une case ☐ à cocher à la main le jour J si besoin.
- **📋 Liste des tables (PDF)** → téléchargement d'un PDF en paysage avec une
  page par table, le nom de la table et la mention "PRIÈRE POUR NOUS" en grand.
  Si une image existe dans `assets/table_images/table_XX.jpg`, elle est ajoutée
  à côté du texte.
- **⬇️ Toutes les invitations (ZIP)** → télécharge un fichier ZIP contenant
  le PDF de chaque invité déjà enregistré, nommé automatiquement :
  - `Invitation de <Nom>.pdf`
- Bouton **Télécharger** et **WhatsApp** sur chaque ligne, pour renvoyer une
  invitation individuellement.
- Bouton **Supprimer** pour corriger une erreur de saisie.

## 5. Important — le bouton "Envoyer sur WhatsApp"

WhatsApp ne permet à aucun site web de joindre un fichier automatiquement à
un message — c'est une restriction volontaire de WhatsApp lui-même (sécurité
et confidentialité), pas une limite de cette application.

Ce que fait donc le bouton :
- **Sur téléphone (Android/iPhone), dans un navigateur récent** : il ouvre le
  menu de partage natif du téléphone avec le PDF déjà prêt — l'invité/admin
  choisit WhatsApp dans la liste, et le fichier est joint automatiquement.
  C'est l'expérience la plus fluide, et elle fonctionne dans la grande
  majorité des cas sur mobile.
- **Sur ordinateur, ou si le partage natif n'est pas disponible** : le bouton
  ouvre une conversation WhatsApp Web avec un message pré-rempli ; il faut
  alors joindre manuellement le PDF téléchargé juste avant (glisser-déposer
  dans WhatsApp). C'est une limite technique de WhatsApp et non de
  l'application.

## 6. Personnaliser les tables

Dans `config.php`, la fonction `saints_list()` contient vos 50 noms. Seuls
les `NB_TABLES` premiers (25 par défaut) sont utilisés comme tables. Pour
changer :
- Le nombre de tables : modifiez `define('NB_TABLES', 25);`
- Le nombre de places par table : modifiez `define('PLACES_PAR_TABLE', 10);`
- L'ordre/le choix des noms utilisés comme tables : réordonnez la liste dans
  `saints_list()`.

## 7. Sécurité

- Le mot de passe admin est à changer avant la mise en ligne (voir étape 1).
- Les dossiers `data/` et `lib/` sont protégés par des fichiers `.htaccess`
  qui bloquent leur accès direct (si votre hébergeur utilise Apache — c'est
  le cas de la plupart des hébergements mutualisés).
- Pensez à faire une copie régulière du fichier `data/mariage.db` (c'est
  toute votre liste d'invités).

## 8. Support technique

Si un écran blanc ou une erreur 500 apparaît après la mise en ligne, la
cause la plus fréquente est une extension PHP manquante chez l'hébergeur
(`pdo_sqlite`, `gd` ou `zip`). La plupart des panneaux d'hébergement
(cPanel, Plesk, hPanel...) permettent de les activer en 1 clic dans
"Sélecteur de version PHP" / "Extensions PHP".
