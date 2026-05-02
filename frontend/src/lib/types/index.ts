export interface User {
	id: number;
	name: string;
	email: string;
	created_at?: string;
	updated_at?: string;
}

export interface ComplaintImage {
	id: number;
	complaint_id: number;
	image_path: string;
	created_at?: string;
}

export type ComplaintStatus = 'pending' | 'process' | 'completed' | 'rejected';

export interface Complaint {
	id: number;
	user_id: number;
	title: string;
	description: string;
	status: ComplaintStatus;
	images?: ComplaintImage[];
	created_at: string;
	updated_at?: string;
}

export interface ApiResponse<T> {
	data: T;
	message?: string;
	meta?: {
		current_page: number;
		last_page: number;
		per_page: number;
		total: number;
	};
}

export interface AuthResponse {
	token: string;
	user: User;
}

export interface LoginRequest {
	email: string;
	password: string;
}

export interface RegisterRequest {
	name: string;
	email: string;
	password: string;
	password_confirmation: string;
}

export interface CreateComplaintRequest {
	title: string;
	description: string;
	images?: File[];
}

export interface ErrorResponse {
	message: string;
	errors?: Record<string, string[]>;
}