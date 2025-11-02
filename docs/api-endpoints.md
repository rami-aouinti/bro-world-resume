# Endpoints de l'API Resume

Ce document synthétise les routes HTTP exposées par le domaine Resume. Chaque workflow repose sur le `userId` (UUID v4 recommandé) qui identifie sans ambiguïté le propriétaire du CV. Toute requête de création ou de mise à jour doit fournir ce `userId`; les projections publiques l'utilisent comme clé de recherche.

## Cas d’usage principaux
1. **Onboarding d’un nouvel utilisateur** : création du CV de référence via `/api/v1/resume`, puis ajout des premières expériences/formations/compétences.
2. **Mise à jour continue** : modifications incrémentales (PATCH) du CV, des expériences, ou des compétences lorsque le membre ajoute une mission ou une certification.
3. **Publication sur le portfolio** : lecture publique du profil via `/api/public/resume/{userId}` consommé par `bro-world-portfolio-main`.
4. **Synchronisation programmée** : commandes de scheduler qui régénèrent les caches et projections pour un `userId` donné.

## Résumé (`/api/v1/resume`)

| Méthode | Chemin | Description | Particularités liées à `userId` |
| --- | --- | --- | --- |
| GET | `/api/v1/resume` | Lister les CV avec filtres, pagination et recherche plein texte. | Filtre possible via `?search=`, pas de filtre direct par `userId` (utiliser `GET /api/v1/resume/{id}`). |
| GET | `/api/v1/resume/{id}` | Obtenir le CV par son identifiant primaire. | La réponse inclut le `userId` qui doit rester unique. |
| POST | `/api/v1/resume` | Créer un CV. | `userId` obligatoire, unique. Toute tentative de réutilisation renvoie HTTP 409. |
| PUT | `/api/v1/resume/{id}` | Remplacer l’intégralité du CV. | Le payload doit reprendre le même `userId` qu’à la création. |
| PATCH | `/api/v1/resume/{id}` | Mise à jour partielle (headline, summary, contact…). | Le DTO valide que le `userId` fourni correspond à celui stocké. |
| DELETE | `/api/v1/resume/{id}` | Supprimer un CV ainsi que ses collections associées. | Libère le `userId` pour une future recréation. |

### Payload JSON (création / mise à jour complète)
```json
{
  "userId": "9a7a5ad7-98b1-4ebc-8dcd-8da4cb7ff65f",
  "fullName": "Alex \"Bro\" Devaux",
  "headline": "Full-stack artisan & indie builder",
  "summary": "Crafting joyful experiences across web and native platforms.",
  "location": "Montréal, QC",
  "email": "hello@bro.dev",
  "phone": "+1 555 0100 200",
  "website": "https://bro.dev",
  "avatarUrl": "https://cdn.example.com/bro/avatar.png"
}
```

### Contraintes principales
- `userId` : UUID valide (`@Assert\Uuid`), unique, non nul.
- `fullName` et `headline` : `@Assert\NotBlank`, longueur ≤ 255 caractères.
- `email`, `website`, `avatarUrl` : contraintes de format (`@Assert\Email`, `@Assert\Url`).
- `phone` : longueur ≤ 64 caractères (format libre).

## Expérience (`/api/v1/experience`)

| Méthode | Chemin | Description | Contraintes `userId` |
| --- | --- | --- | --- |
| GET | `/api/v1/experience` | Lister les expériences (filtrables par critères génériques). | Réservé aux usages internes (jointure via `resumeId`). |
| GET | `/api/v1/experience/{id}` | Obtenir une expérience. | Retourne le `resumeId` et le `userId` associé. |
| POST | `/api/v1/experience` | Créer une expérience liée à un CV. | `userId` et `resumeId` obligatoires et doivent correspondre. |
| PATCH | `/api/v1/experience/{id}` | Mise à jour partielle. | Les hooks refusent tout `userId` différent de celui du CV parent. |
| DELETE | `/api/v1/experience/{id}` | Supprimer une expérience. | Libère la position (`position`) dans l’ordre d’affichage. |

### Payload JSON minimal pour la création
```json
{
  "userId": "9a7a5ad7-98b1-4ebc-8dcd-8da4cb7ff65f",
  "resumeId": "b4de4b08-6e8f-4ebd-a6e7-4b797d7d8c92",
  "company": "Bro World Studios",
  "role": "Founder & Principal Engineer",
  "startDate": "2019-01-01",
  "endDate": null,
  "isCurrent": true,
  "position": 0,
  "location": "Remote",
  "description": "Leading product, code and community initiatives."
}
```

### Validation
- `resumeId` : UUID existant. Retourne HTTP 404 si le CV n’existe pas.
- `userId` : doit matcher le `userId` du CV (sinon HTTP 400 « The provided userId does not match the resume owner. »).
- `startDate` / `endDate` : format `YYYY-MM-DD`. Si `isCurrent = true`, `endDate` est automatiquement vidée.
- `position` : entier ≥ 0, utilisé pour l’ordre d’affichage.

## Formation (`/api/v1/education`)

| Méthode | Chemin | Description | Contraintes |
| --- | --- | --- | --- |
| POST | `/api/v1/education` | Créer une formation associée à un CV. | `userId` + `resumeId` obligatoires et cohérents. |
| PATCH | `/api/v1/education/{id}` | Mise à jour partielle. | Garder le même `userId` que le CV. |
| DELETE | `/api/v1/education/{id}` | Supprimer une formation. | Cascade sur les projections publiques. |

### Payload JSON
```json
{
  "userId": "9a7a5ad7-98b1-4ebc-8dcd-8da4cb7ff65f",
  "resumeId": "b4de4b08-6e8f-4ebd-a6e7-4b797d7d8c92",
  "school": "École Bro de Technologie",
  "degree": "MSc Software Engineering",
  "field": "Cloud-native architectures",
  "startDate": "2012-09-01",
  "endDate": "2014-06-01",
  "isCurrent": false,
  "position": 0,
  "description": "Thesis on resilient cloud-native architectures."
}
```

Validation similaire à `/api/v1/experience` (UUID + cohérence `userId`).

## Compétence (`/api/v1/skill`)

| Méthode | Chemin | Description | Contraintes |
| --- | --- | --- | --- |
| POST | `/api/v1/skill` | Créer une compétence liée à un CV. | `userId` + `resumeId` obligatoires. |
| PATCH | `/api/v1/skill/{id}` | Mise à jour partielle. | `userId` doit rester aligné. |
| DELETE | `/api/v1/skill/{id}` | Supprimer une compétence. | Mise à jour du tri par `position`. |

### Payload JSON
```json
{
  "userId": "9a7a5ad7-98b1-4ebc-8dcd-8da4cb7ff65f",
  "resumeId": "b4de4b08-6e8f-4ebd-a6e7-4b797d7d8c92",
  "name": "Symfony",
  "category": "Backend",
  "level": "expert",
  "position": 0
}
```

## Projections publiques (`/api/public/resume`)

| Méthode | Chemin | Description | Réponse |
| --- | --- | --- | --- |
| GET | `/api/public/resume/{userId}` | Projection complète (CV + expériences + formations + compétences). | Objet JSON structuré avec les sections `resume`, `experiences`, `education`, `skills`. |
| GET | `/api/public/resume/{userId}/experiences` | Liste ordonnée des expériences. | Tableau JSON trié par `position`. |
| GET | `/api/public/resume/{userId}/education` | Liste ordonnée des formations. | Tableau JSON trié par `position`. |
| GET | `/api/public/resume/{userId}/skills` | Liste ordonnée des compétences. | Tableau JSON trié par `position`. |

### Exemple de réponse (`GET /api/public/resume/{userId}`)
```json
{
  "resume": {
    "id": "b4de4b08-6e8f-4ebd-a6e7-4b797d7d8c92",
    "userId": "9a7a5ad7-98b1-4ebc-8dcd-8da4cb7ff65f",
    "fullName": "Alex \"Bro\" Devaux",
    "headline": "Full-stack artisan & indie builder",
    "summary": "Crafting joyful experiences across web and native platforms.",
    "location": "Montréal, QC",
    "email": "hello@bro.dev",
    "phone": "+1 555 0100 200",
    "website": "https://bro.dev",
    "avatarUrl": "https://cdn.example.com/bro/avatar.png",
    "createdAt": "2025-02-12T12:00:00+00:00",
    "updatedAt": "2025-02-12T12:30:00+00:00"
  },
  "experiences": [
    {
      "id": "d1aab6ac-6f28-4a3a-b709-a33a9800c2fb",
      "resumeId": "b4de4b08-6e8f-4ebd-a6e7-4b797d7d8c92",
      "company": "Bro World Studios",
      "role": "Founder & Principal Engineer",
      "startDate": "2019-01-01",
      "endDate": null,
      "isCurrent": true,
      "position": 0,
      "location": "Remote",
      "description": "Leading product, code and community initiatives."
    }
  ],
  "education": [
    {
      "id": "e4f781bc-6b5f-4ed7-b9d5-13f48eac68f1",
      "resumeId": "b4de4b08-6e8f-4ebd-a6e7-4b797d7d8c92",
      "school": "École Bro de Technologie",
      "degree": "MSc Software Engineering",
      "field": "Cloud-native architectures",
      "startDate": "2012-09-01",
      "endDate": "2014-06-01",
      "isCurrent": false,
      "position": 0,
      "description": "Thesis on resilient cloud-native architectures."
    }
  ],
  "skills": [
    {
      "id": "f5c6e340-940c-4aa5-b05c-0d8046dd5aa4",
      "resumeId": "b4de4b08-6e8f-4ebd-a6e7-4b797d7d8c92",
      "name": "Symfony",
      "category": "Backend",
      "level": "expert",
      "position": 0
    }
  ]
}
```

### Erreurs courantes
- `404 Resume not found for provided userId.` lorsque aucune donnée n’est trouvée pour le `userId` demandé.
- `400 The provided userId does not match the resume owner.` si une ressource fille (expérience/formation/compétence) référence un `userId` différent de celui du CV parent.
- `409` lors de la création d’un CV avec un `userId` déjà présent dans la base.

## Commandes planifiées
Une migration (`Version20240901000000`) enregistre les commandes `resume:cache:refresh` dans la table `scheduled_command`. Ces jobs doivent être activés dans les environnements où le portfolio consomme l’API pour garantir une projection fraîche.

| Nom | Commande | Arguments | Cron | Rôle |
| --- | --- | --- | --- | --- |
| `resume_cache_refresh_profile` | `resume:cache:refresh` | `--scope=profile` | `*/10 * * * *` | Actualise la projection complète pour chaque `userId` actif. |
| `resume_cache_refresh_public` | `resume:cache:refresh` | `--scope=public` | `0 * * * *` | Régénère les caches partagés exploités par `bro-world-portfolio-main`. |

Chaque exécution doit itérer sur les `userId` présents en base ; les payloads générés sont ensuite consommés côté frontend.
