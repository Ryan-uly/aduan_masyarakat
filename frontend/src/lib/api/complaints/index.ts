import { api } from '$lib/api/client';
import type { Complaint, ApiResponse, CreateComplaintRequest } from '$lib/types';

export function getComplaints() {
	return api.get<ApiResponse<Complaint[]>>('/complaints');
}

export function getComplaint(id: number) {
	return api.get<ApiResponse<Complaint>>(`/complaints/${id}`);
}

export function createComplaint(data: CreateComplaintRequest) {
	return api.post<ApiResponse<Complaint>>('/complaints', data);
}

export function updateComplaint(id: number, data: Partial<CreateComplaintRequest>) {
	return api.put<ApiResponse<Complaint>>(`/complaints/${id}`, data);
}

export function deleteComplaint(id: number) {
	return api.delete<void>(`/complaints/${id}`);
}