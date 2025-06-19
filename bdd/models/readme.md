# ?? MongoDB Schema Design � Justification & Mapping

Ce document explique les **d�cisions de mod�lisation** prises lors de la conversion de la base SQL relationnelle vers un mod�le orient� documents MongoDB. Chaque entit� est analys�e avec les raisons du choix entre **embedding** ou **referencing**.

---

## ????? `users` Collection

### ? Mod�lis� comme : Collection d�di�e

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
L'utilisateur est une entit� centrale ? collection ind�pendante

Les relations avec les jeux et les rooms sont r�f�renc�es pour �viter la duplication

Les amiti�s sont embedd�es car :

    Lecture fr�quente depuis le profil

    Statuts (pending, accepted, blocked) simples

    Mises � jour l�g�res et peu fr�quentes


## ?? games Collection

### ? Mod�lis� comme : Collection ind�pendante

```json
{
  "_id": ObjectId,
  "name": "Tetris",
  "image": "/games/tetris.jpg",
  "description": "Un jeu classique."
}

```

### ?? Raisons
Entit�s partag�es entre utilisateurs et rooms

Peu de donn�es et de modifications ? r�f�rencement recommand�

Permet d�ajouter des champs enrichis sans duplication


## ?? rooms Collection

### ? Mod�lis� comme : Collection ind�pendante

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
Entit�s isol�es avec logique m�tier ? collection � part

Participants (ex-room_user) list�s par ID :

    Embedding d�IDs simple
    
    Lecture group�e fr�quente ? performance accrue


## ?? friendships dans users

### ? Mod�lis� comme : Embedded array dans users

```json

{
"friendships": [
  { "friend_id": ObjectId, "status": "accepted" }
]
}

```

### ?? Raisons
La table SQL friendship contient un status utile mais simple

Acc�s direct depuis le profil utilisateur ? embedding performant

Option : si logique complexe (logs, historique), passer � une collection s�par�e


## ?? user_game ? Champs games dans users

### ? Mod�lis� comme : R�f�rencement dans un tableau

```json

"games": [ObjectId]

```

### ?? Raisons
Relation N:N simple, sans m�tadonn�es ? array d�ObjectIds suffisant

�vite les jointures co�teuses

Facile � enrichir si besoin :


## ?? room_user ? Inclus dans rooms.participant
### ? Mod�lis� comme : Tableau participants dans rooms

```json

"participants": [ObjectId]

```

### ?? Raisons
Pas de m�tadonn�es dans room_user ? embedding suffisant

Lecture directe des utilisateurs d�une room facilit�e

Facile � indexer pour recherches invers�es



