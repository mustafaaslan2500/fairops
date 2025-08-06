// API Response Types

export interface LoginRequest {
  email: string;
  password: string;
}

export interface GoogleLoginRequest {
  access_token: string;
}

export interface UserData {
  id: number;
  name: string;
  surname: string;
  email: string;
  phone: string | null;
  is_admin: boolean;
  token: string;
}

export interface LoginSuccessResponse {
  status: true;
  message: string;
  user_data: UserData;
}

export interface LoginErrorResponse {
  status: false;
  message: string;
}

export type LoginResponse = LoginSuccessResponse | LoginErrorResponse;

export interface ApiError {
  message: string;
  status?: number;
}
