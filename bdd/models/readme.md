# ?? MongoDB Schema Design – Justification & Mapping

Ce document explique les **décisions de modélisation** prises lors de la conversion de la base SQL relationnelle vers un modèle orienté documents MongoDB. Chaque entité est analysée avec les raisons du choix entre **embedding** ou **referencing**.

---

## ????? `users` Collection

### ? Modélisé comme : Collection dédiée

```json
{
  "_id": ObjectId,
  "email": "john@example.com",
  "username": "john",
  "password": "...",
  "created_at": ISODate,
  "avatar": "/img/john.png",
  "roles": ["ROLE_USER"],
  "Favoris": [ObjectId],
  "friendships": [
    { "friend_id": ObjectId, "status": "accepted" }
  ]
}

```

### ?? Raisons
L'utilisateur est une entité centrale ? collection indépendante

Les relations avec les jeux et les rooms sont référencées pour éviter la duplication

Les amitiés sont embeddées car :

    Lecture fréquente depuis le profil

    Statuts (pending, accepted, blocked) simples

    Mises à jour légères et peu fréquentes


## ?? games Collection

### ? Modélisé comme : Collection indépendante

```json
{
  "_id": ObjectId,
  "name": "Tetris",
  "image": "/games/tetris.jpg",
  "description": "Un jeu classique."
}

```

### ?? Raisons
Entités partagées entre utilisateurs et rooms

Peu de données et de modifications ? référencement recommandé

Permet d’ajouter des champs enrichis sans duplication


## ?? rooms Collection

### ? Modélisé comme : Collection indépendante

```json

{
  "_id": ObjectId,
  "name": "Salle 1",
  "slug": "salle-1",
  "created_at": ISODate,
  "owner_id": ObjectId,
  "game_type": "duo",
  "game_id": ObjectId,
  "participants": [ObjectId]
}

```

### ?? Raisons
Entités isolées avec logique métier ? collection à part

Participants (ex-room_user) listés par ID :

    Embedding d’IDs simple
    
    Lecture groupée fréquente ? performance accrue


## ?? friendships dans users

### ? Modélisé comme : Embedded array dans users

```json

{
"friendships": [
  { "friend_id": ObjectId, "status": "accepted" }
]
}

```

### ?? Raisons
La table SQL friendship contient un status utile mais simple

Accès direct depuis le profil utilisateur ? embedding performant

Option : si logique complexe (logs, historique), passer à une collection séparée


## ?? user_game ? Champs games dans users

### ? Modélisé comme : Référencement dans un tableau

```json

"games": [ObjectId]

```

### ?? Raisons
Relation N:N simple, sans métadonnées ? array d’ObjectIds suffisant

Évite les jointures coûteuses

Facile à enrichir si besoin :


## ?? room_user ? Inclus dans rooms.participant
### ? Modélisé comme : Tableau participants dans rooms

```json

"participants": [ObjectId]

```

### ?? Raisons
Pas de métadonnées dans room_user ? embedding suffisant

Lecture directe des utilisateurs d’une room facilitée

Facile à indexer pour recherches inversées



