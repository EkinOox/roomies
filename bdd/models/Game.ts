import mongoose, { Schema, Document, Model } from 'mongoose'

export interface IGame extends Document {
  name: string
  image?: string
  description?: string
}

const GameSchema = new Schema<IGame>({
  name: { type: String, required: true },
  image: { type: String },
  description: { type: String }
})

export const GameModel: Model<IGame> = mongoose.model<IGame>('Game', GameSchema)
