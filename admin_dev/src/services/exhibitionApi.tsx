import axios from "axios";
import type { 
  Exhibition, 
  CreateExhibitionRequest, 
  UpdateExhibitionRequest,
  ExhibitionListResponse,
  ExhibitionDetailResponse,
  ExhibitionCreateResponse,
  ExhibitionUpdateResponse,
  Participant,
  Decorator,
  Document
} from "../types/exhibition";

// Mock services import
import { 
  mockExhibitionService, 
  mockParticipantService, 
  mockDecoratorService, 
  mockDocumentService 
} from "./mockApi";

const instance = axios.create({
  baseURL: import.meta.env.DEV ? "/api" : "https://api.fairops.com.tr",
  headers: {
    "Content-Type": "application/json",
  },
  timeout: 10000,
  withCredentials: false,
});

// Auth interceptor
instance.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Development modunda mock servisleri, production'da gerçek API'yi kullan
const USE_MOCK_API = import.meta.env.DEV; // Development'ta mock kullan

// Exhibition/Company Management API
export const exhibitionService = {
  // Get all exhibitions
  getExhibitions: async (page = 1, limit = 10, search = ""): Promise<ExhibitionListResponse> => {
    if (USE_MOCK_API) {
      return mockExhibitionService.getExhibitions(page, limit, search);
    }
    
    try {
      const response = await instance.get<ExhibitionListResponse>("/admin/exhibitions", {
        params: { page, limit, search }
      });
      return response.data;
    } catch (error: any) {
      console.error("Get exhibitions error:", error);
      return {
        status: false,
        message: "Fuar/Firma listesi alınamadı.",
        data: []
      };
    }
  },

  // Get exhibition by ID
  getExhibition: async (id: number): Promise<ExhibitionDetailResponse> => {
    if (USE_MOCK_API) {
      return mockExhibitionService.getExhibition(id);
    }
    
    try {
      const response = await instance.get<ExhibitionDetailResponse>(`/admin/exhibitions/${id}`);
      return response.data;
    } catch (error: any) {
      console.error("Get exhibition error:", error);
      return {
        status: false,
        message: "Fuar/Firma detayı alınamadı.",
        data: {} as Exhibition
      };
    }
  },

  // Create new exhibition
  createExhibition: async (data: CreateExhibitionRequest): Promise<ExhibitionCreateResponse> => {
    if (USE_MOCK_API) {
      return mockExhibitionService.createExhibition(data);
    }
    
    try {
      const response = await instance.post<ExhibitionCreateResponse>("/admin/exhibitions", data);
      return response.data;
    } catch (error: any) {
      console.error("Create exhibition error:", error);
      return {
        status: false,
        message: "Fuar/Firma oluşturulamadı."
      };
    }
  },

  // Update exhibition
  updateExhibition: async (data: UpdateExhibitionRequest): Promise<ExhibitionUpdateResponse> => {
    if (USE_MOCK_API) {
      return mockExhibitionService.updateExhibition(data);
    }
    
    try {
      const response = await instance.put<ExhibitionUpdateResponse>(`/admin/exhibitions/${data.id}`, data);
      return response.data;
    } catch (error: any) {
      console.error("Update exhibition error:", error);
      return {
        status: false,
        message: "Fuar/Firma güncellenemedi."
      };
    }
  },

  // Delete exhibition
  deleteExhibition: async (id: number): Promise<{ status: boolean; message: string }> => {
    if (USE_MOCK_API) {
      return mockExhibitionService.deleteExhibition(id);
    }
    
    try {
      const response = await instance.delete(`/admin/exhibitions/${id}`);
      return response.data;
    } catch (error: any) {
      console.error("Delete exhibition error:", error);
      return {
        status: false,
        message: "Fuar/Firma silinemedi."
      };
    }
  }
};

// Participant Tracking API (Read-only)
export const participantService = {
  getParticipants: async (page = 1, limit = 10, exhibition_id?: number): Promise<{ status: boolean; data: Participant[]; message: string }> => {
    if (USE_MOCK_API) {
      return mockParticipantService.getParticipants(page, limit, exhibition_id);
    }
    
    try {
      const response = await instance.get("/admin/participants", {
        params: { page, limit, exhibition_id }
      });
      return response.data;
    } catch (error: any) {
      console.error("Get participants error:", error);
      return {
        status: false,
        message: "Katılımcı listesi alınamadı.",
        data: []
      };
    }
  }
};

// Decorator Tracking API (Read-only)
export const decoratorService = {
  getDecorators: async (page = 1, limit = 10, exhibition_id?: number): Promise<{ status: boolean; data: Decorator[]; message: string }> => {
    if (USE_MOCK_API) {
      return mockDecoratorService.getDecorators(page, limit, exhibition_id);
    }
    
    try {
      const response = await instance.get("/admin/decorators", {
        params: { page, limit, exhibition_id }
      });
      return response.data;
    } catch (error: any) {
      console.error("Get decorators error:", error);
      return {
        status: false,
        message: "Dekoratör listesi alınamadı.",
        data: []
      };
    }
  }
};

// Document Tracking API (Read-only)
export const documentService = {
  getDocuments: async (page = 1, limit = 10, exhibition_id?: number): Promise<{ status: boolean; data: Document[]; message: string }> => {
    if (USE_MOCK_API) {
      return mockDocumentService.getDocuments(page, limit, exhibition_id);
    }
    
    try {
      const response = await instance.get("/admin/documents", {
        params: { page, limit, exhibition_id }
      });
      return response.data;
    } catch (error: any) {
      console.error("Get documents error:", error);
      return {
        status: false,
        message: "Belge listesi alınamadı.",
        data: []
      };
    }
  }
};
