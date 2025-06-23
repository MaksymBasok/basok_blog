import type PostUser from "~/types/Post/PostUser";
import type PostCategory from "~/types/Post/PostCategory";

export default interface Post {
    category_id: number
    content_html: string
    content_raw: string
    created_at: string
    deleted_at: string | null
    excerpt: string
    id: number
    is_published: boolean
    published_at: string | null
    slug: string
    title: string
    updated_at: string
    user_id: number
    user: PostUser
    category: PostCategory
}
