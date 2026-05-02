import { writable } from 'svelte/store';
import type { User } from '$lib/types';

interface AuthState {
	token: string | null;
	user: User | null;
	isAuthenticated: boolean;
	isLoading: boolean;
}

function createAuthStore() {
	const getInitialState = (): AuthState => ({
		token: typeof window !== 'undefined' ? localStorage.getItem('token') : null,
		user: null,
		isAuthenticated: !!localStorage.getItem('token'),
		isLoading: true
	});

	const { subscribe, set, update } = writable<AuthState>(getInitialState());

	return {
		subscribe,
		setUser: (user: User) => update((s) => ({ ...s, user, isLoading: false })),
		setToken: (token: string) => {
			if (typeof window !== 'undefined') {
				localStorage.setItem('token', token);
			}
			update((s) => ({ ...s, token, isAuthenticated: true }));
		},
		logout: () => {
			if (typeof window !== 'undefined') {
				localStorage.removeItem('token');
			}
			set({ token: null, user: null, isAuthenticated: false, isLoading: false });
		},
		setLoading: (isLoading: boolean) => update((s) => ({ ...s, isLoading }))
	};
}

export const auth = createAuthStore();