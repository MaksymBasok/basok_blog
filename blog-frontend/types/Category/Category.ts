export default interface Category {
    "id": number
    "parent_id": number
    "slug": string
    "title": string
    "description": string
    "created_at": string | null
    "updated_at": string | null
    "deleted_at": string | null
    "parent_category": Category
}
