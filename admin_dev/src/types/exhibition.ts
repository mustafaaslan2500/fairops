// Exhibition/Company Management Types

export interface Exhibition {
  id: number;
  name: string;
  responsible_person_name: string;
  responsible_person_surname: string;
  responsible_person_email: string;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

export interface CreateExhibitionRequest {
  name: string;
  responsible_person_name: string;
  responsible_person_surname: string;
  responsible_person_email: string;
}

export interface UpdateExhibitionRequest {
  id: number;
  name?: string;
  responsible_person_name?: string;
  responsible_person_surname?: string;
  responsible_person_email?: string;
  status?: 'active' | 'inactive';
}

export interface ExhibitionListResponse {
  status: boolean;
  message: string;
  data: Exhibition[];
  total?: number;
  page?: number;
  limit?: number;
}

export interface ExhibitionDetailResponse {
  status: boolean;
  message: string;
  data: Exhibition;
}

export interface ExhibitionCreateResponse {
  status: boolean;
  message: string;
  data?: Exhibition;
}

export interface ExhibitionUpdateResponse {
  status: boolean;
  message: string;
  data?: Exhibition;
}

// Participant Types (Read-only for admin)
export interface Participant {
  id: number;
  exhibition_id: number;
  exhibition_name: string;
  name: string;
  email: string;
  status: 'active' | 'registered' | 'incomplete' | 'pending';
  created_at: string;
}

// Decorator Types (Read-only for admin)
export interface Decorator {
  id: number;
  exhibition_id: number;
  exhibition_name: string;
  company_name: string;
  assigned_participants: string[];
  status: 'assigned' | 'working' | 'completed';
  created_at: string;
}

// Document Types (Read-only for admin)
export interface Document {
  id: number;
  exhibition_id: number;
  exhibition_name: string;
  type: 'design' | 'static_report' | 'contract' | 'technical_plan' | 'safety_document';
  filename: string;
  uploaded_at: string;
  status: 'uploaded' | 'approved' | 'rejected' | 'pending';
}
