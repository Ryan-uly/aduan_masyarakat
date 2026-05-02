import { api } from '$lib/api/client';
import type { AuthResponse } from '$lib/types';

export async function registerRequest(
    name: string,
    email: string,
    password: string,
    password_confirmation: string
): Promise<AuthResponse> {
	return api.post<AuthResponse>('/register', {
		name,
		email,
		password,
		password_confirmation
	}, false);
}