import type { ApiResponse, ErrorResponse } from '$lib/types';

class ApiClient {
	private baseUrl: string;

	constructor() {
		this.baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';
	}

	private getToken(): string | null {
		if (typeof window === 'undefined') return null;
		return localStorage.getItem('token');
	}

	private getHeaders(includeAuth = true): HeadersInit {
		const headers: HeadersInit = {
			'Content-Type': 'application/json',
			Accept: 'application/json'
		};

		if (includeAuth) {
			const token = this.getToken();
			if (token) {
				headers['Authorization'] = `Bearer ${token}`;
			}
		}

		return headers;
	}

	private async handleResponse<T>(res: Response): Promise<T> {
		const data = await res.json();

		if (!res.ok) {
			const error: ErrorResponse = {
				message: data.message || 'Terjadi kesalahan'
			};
			throw new Error(error.message);
		}

		return data;
	}

	async get<T>(endpoint: string, includeAuth = true): Promise<T> {
		const res = await fetch(`${this.baseUrl}${endpoint}`, {
			method: 'GET',
			headers: this.getHeaders(includeAuth)
		});
		return this.handleResponse<T>(res);
	}

	async post<T>(endpoint: string, body: unknown, includeAuth = true): Promise<T> {
		const res = await fetch(`${this.baseUrl}${endpoint}`, {
			method: 'POST',
			headers: this.getHeaders(includeAuth),
			body: JSON.stringify(body)
		});
		return this.handleResponse<T>(res);
	}

	async put<T>(endpoint: string, body: unknown, includeAuth = true): Promise<T> {
		const res = await fetch(`${this.baseUrl}${endpoint}`, {
			method: 'PUT',
			headers: this.getHeaders(includeAuth),
			body: JSON.stringify(body)
		});
		return this.handleResponse<T>(res);
	}

	async delete<T>(endpoint: string, includeAuth = true): Promise<T> {
		const res = await fetch(`${this.baseUrl}${endpoint}`, {
			method: 'DELETE',
			headers: this.getHeaders(includeAuth)
		});
		return this.handleResponse<T>(res);
	}

	isAuthenticated(): boolean {
		return !!this.getToken();
	}

	clearToken(): void {
		localStorage.removeItem('token');
	}
}

export const api = new ApiClient();