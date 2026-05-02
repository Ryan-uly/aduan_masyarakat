import { api } from '$lib/api/client';
import type { AuthResponse } from '$lib/types';

export async function loginRequest(email: string, password: string): Promise<AuthResponse> {
	return api.post<AuthResponse>('/login', { email, password }, false);
}