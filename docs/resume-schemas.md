# API Résumé – Schémas d'entrée et de sortie

Ce document synthétise les structures attendues pour les endpoints mis à disposition du portfolio et du back-office autour du CV.

## Projection publique `/api/public/resume/{userId}`

### Réponse (200)

```json
{
  "resume": {
    "id": "3f4b1320-5a91-11ee-9c8a-0242ac130002",
    "userId": "4c3a7f10-5d5c-4d1f-9a3a-6b9c2d2d5b10",
    "fullName": "Alex \"Bro\" Devaux",
    "headline": "Full-stack artisan & indie builder",
    "summary": "Crafting joyful experiences across web and native platforms...",
    "location": "Montréal, QC",
    "email": "hello@bro.dev",
    "phone": "+1 555 0100 200",
    "website": "https://bro.dev",
    "avatarUrl": "https://cdn.example.com/bro/avatar.png",
    "createdAt": "2024-12-01T10:15:00+00:00",
    "updatedAt": "2025-02-01T08:42:00+00:00"
  },
  "experiences": [
    {
      "id": "e1bf41ac-8c2f-11ee-9c8a-0242ac130002",
      "resumeId": "3f4b1320-5a91-11ee-9c8a-0242ac130002",
      "company": "Bro World Studios",
      "role": "Founder & Principal Engineer",
      "startDate": "2019-01-01",
      "endDate": null,
      "isCurrent": true,
      "position": 0,
      "location": "Remote",
      "description": "Leading product, code and community initiatives across the Bro ecosystem."
    }
  ],
  "education": [
    {
      "id": "c3f6cfbe-8c2f-11ee-9c8a-0242ac130002",
      "resumeId": "3f4b1320-5a91-11ee-9c8a-0242ac130002",
      "school": "École Bro de Technologie",
      "degree": "MSc Software Engineering",
      "field": null,
      "startDate": "2012-09-01",
      "endDate": "2014-06-01",
      "isCurrent": false,
      "position": 0,
      "description": "Thesis on resilient cloud-native architectures."
    }
  ],
  "skills": [
    {
      "id": "5a821100-8c30-11ee-9c8a-0242ac130002",
      "resumeId": "3f4b1320-5a91-11ee-9c8a-0242ac130002",
      "name": "Symfony",
      "category": "Backend",
      "level": "expert",
      "position": 0
    }
  ]
}
```

## Création d'un CV `/api/v1/resume`

### Corps de requête (POST)

```json
{
  "userId": "7e9e5ec0-0ce2-4a5c-96be-a7721f02d36b",
  "fullName": "Jamie Portfolio",
  "headline": "Product designer & motion tinkerer",
  "summary": "Designing intuitive journeys with a fondness for delightful micro-interactions.",
  "location": "Lisbon, PT",
  "email": "jamie@example.com",
  "phone": "+351 555 011",
  "website": "https://jamie.example",
  "avatarUrl": "https://cdn.example.com/jamie.png"
}
```

### Réponse (201)

```json
{
  "id": "9d61a524-02bc-4d46-87dc-d2f4e5b04f7e",
  "userId": "7e9e5ec0-0ce2-4a5c-96be-a7721f02d36b",
  "fullName": "Jamie Portfolio",
  "headline": "Product designer & motion tinkerer",
  "summary": "Designing intuitive journeys with a fondness for delightful micro-interactions.",
  "location": "Lisbon, PT",
  "email": "jamie@example.com",
  "phone": "+351 555 011",
  "website": "https://jamie.example",
  "avatarUrl": "https://cdn.example.com/jamie.png"
}
```

## Création d'une expérience `/api/v1/experience`

Le champ `resumeId` doit référencer un CV existant pour le même `userId`.

### Corps de requête (POST)

```json
{
  "userId": "4c3a7f10-5d5c-4d1f-9a3a-6b9c2d2d5b10",
  "resumeId": "3f4b1320-5a91-11ee-9c8a-0242ac130002",
  "company": "Bro Ventures",
  "role": "Advisory Engineer",
  "startDate": "2021-05-01",
  "endDate": null,
  "isCurrent": true,
  "position": 2,
  "location": "Remote",
  "description": "Guiding new squads on developer experience best practices."
}
```

### Réponse (201)

```json
{
  "id": "cc9e3d92-8c30-11ee-9c8a-0242ac130002",
  "resumeId": "3f4b1320-5a91-11ee-9c8a-0242ac130002",
  "company": "Bro Ventures",
  "role": "Advisory Engineer",
  "startDate": "2021-05-01",
  "endDate": null,
  "isCurrent": true,
  "position": 2,
  "location": "Remote",
  "description": "Guiding new squads on developer experience best practices."
}
```

Les mêmes conventions s'appliquent pour `/api/v1/education` (champs `school`, `degree`, `field`, `startDate`, `endDate`, `isCurrent`, `position`, `description`) et `/api/v1/skill` (champs `name`, `category`, `level`, `position`).

## Codes d'erreur principaux

| Statut | Motif |
| --- | --- |
| 400 | `resumeId` absent ou `userId` incompatible avec le CV associé. |
| 404 | CV introuvable pour l’`userId` fourni. |
| 422 | Violation de contraintes de validation (longueur, format d’email/URL, dates). |

