import mongoose, { Schema, Document, Model, Types } from 'mongoose'

export interface IUser extends Document {
  email: string
  username: string
  password: string
  created_at: Date
  avatar?: string
  roles: string[]
  favoritesGames: Types.ObjectId[]
  friendships: {
    friend_id: Types.ObjectId
    status: 'pending' | 'accepted' | 'rejected' | 'blocked'
  }[]
}

// Schéma Mongoose
const UserSchema = new Schema<IUser>({
  email: { type: String, required: true, unique: true },
  username: { type: String, required: true },
  password: { type: String, required: true },
  created_at: { type: Date, default: Date.now },
  avatar: { type: String },
  roles: [{ type: String, default: ['ROLE_USER'] }],
  favoritesGames: [{ type: Schema.Types.ObjectId, ref: 'Game' }],
  friendships: [
    {
      friend_id: { type: Schema.Types.ObjectId, ref: 'User' },
      status: { type: String, enum: ['pending', 'accepted', 'rejected', 'blocked'], default: 'pending' }
    }
  ]
})

export const UserModel: Model<IUser> = mongoose.model<IUser>('User', UserSchema)
