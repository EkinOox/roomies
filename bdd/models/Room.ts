import mongoose, { Schema, Document, Model, Types } from 'mongoose'

export interface IRoom extends Document {
  name: string
  slug: string
  created_at: Date
  game_type: string
  owner_id: Types.ObjectId
  game_id: Types.ObjectId
  participants: Types.ObjectId[]
}

const RoomSchema = new Schema<IRoom>({
  name: { type: String, required: true },
  slug: { type: String, required: true, unique: true },
  created_at: { type: Date, default: Date.now },
  game_type: { type: String, default: 'duo' },
  owner_id: { type: Schema.Types.ObjectId, ref: 'User', required: true },
  game_id: { type: Schema.Types.ObjectId, ref: 'Game', required: true },
  participants: [{ type: Schema.Types.ObjectId, ref: 'User' }]
})

export const RoomModel: Model<IRoom> = mongoose.model<IRoom>('Room', RoomSchema)
