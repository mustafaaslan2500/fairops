// Mock data ve demo API servisleri
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

// Demo veriler
const mockExhibitions: Exhibition[] = [
  {
    id: 1,
    name: "İstanbul Fuar Merkezi",
    responsible_person_name: "Ahmet",
    responsible_person_surname: "Yılmaz",
    responsible_person_email: "ahmet.yilmaz@istanbulfuar.com",
    status: "active",
    created_at: "2024-01-15T10:30:00Z",
    updated_at: "2024-08-01T15:20:00Z"
  },
  {
    id: 2,
    name: "Ankara İş Fuarı",
    responsible_person_name: "Elif",
    responsible_person_surname: "Demir",
    responsible_person_email: "elif.demir@ankarafuar.com",
    status: "active",
    created_at: "2024-02-20T09:15:00Z",
    updated_at: "2024-07-28T11:45:00Z"
  },
  {
    id: 3,
    name: "İzmir Ticaret Odası",
    responsible_person_name: "Mehmet",
    responsible_person_surname: "Kaya",
    responsible_person_email: "mehmet.kaya@izto.org.tr",
    status: "inactive",
    created_at: "2024-03-10T14:20:00Z",
    updated_at: "2024-06-15T16:30:00Z"
  },
  {
    id: 4,
    name: "Bursa Tekstil Fuarı",
    responsible_person_name: "Ayşe",
    responsible_person_surname: "Çelik",
    responsible_person_email: "ayse.celik@bursatekstil.com",
    status: "active",
    created_at: "2024-04-05T08:45:00Z",
    updated_at: "2024-08-02T10:15:00Z"
  },
  {
    id: 5,
    name: "Antalya Turizm Expo",
    responsible_person_name: "Can",
    responsible_person_surname: "Öztürk",
    responsible_person_email: "can.ozturk@antalyaexpo.com",
    status: "active",
    created_at: "2024-05-12T13:10:00Z",
    updated_at: "2024-07-30T17:25:00Z"
  }
];

const mockParticipants: Participant[] = [
  {
    id: 1,
    exhibition_id: 1,
    exhibition_name: "İstanbul Fuar Merkezi",
    name: "ABC Teknoloji",
    email: "info@abcteknoloji.com",
    status: "active",
    created_at: "2024-06-01T10:00:00Z"
  },
  {
    id: 2,
    exhibition_id: 1,
    exhibition_name: "İstanbul Fuar Merkezi",
    name: "XYZ Şirketi",
    email: "contact@xyz.com",
    status: "registered",
    created_at: "2024-06-15T14:30:00Z"
  },
  {
    id: 3,
    exhibition_id: 2,
    exhibition_name: "Ankara İş Fuarı",
    name: "DEF Mühendislik",
    email: "info@defmuh.com",
    status: "incomplete",
    created_at: "2024-07-01T09:20:00Z"
  },
  {
    id: 4,
    exhibition_id: 2,
    exhibition_name: "Ankara İş Fuarı",
    name: "GHI İnşaat",
    email: "iletisim@ghiinsaat.com",
    status: "pending",
    created_at: "2024-07-20T16:45:00Z"
  },
  {
    id: 5,
    exhibition_id: 4,
    exhibition_name: "Bursa Tekstil Fuarı",
    name: "JKL Tekstil",
    email: "satis@jkltekstil.com",
    status: "active",
    created_at: "2024-08-01T11:15:00Z"
  }
];

const mockDecorators: Decorator[] = [
  {
    id: 1,
    exhibition_id: 1,
    exhibition_name: "İstanbul Fuar Merkezi",
    company_name: "Mega Dekorasyon",
    assigned_participants: ["ABC Teknoloji", "XYZ Şirketi"],
    status: "working",
    created_at: "2024-06-05T12:00:00Z"
  },
  {
    id: 2,
    exhibition_id: 2,
    exhibition_name: "Ankara İş Fuarı",
    company_name: "Elit Stand",
    assigned_participants: ["DEF Mühendislik"],
    status: "assigned",
    created_at: "2024-07-10T15:30:00Z"
  },
  {
    id: 3,
    exhibition_id: 4,
    exhibition_name: "Bursa Tekstil Fuarı",
    company_name: "Pro Dekor",
    assigned_participants: ["JKL Tekstil"],
    status: "completed",
    created_at: "2024-08-02T08:20:00Z"
  }
];

const mockDocuments: Document[] = [
  {
    id: 1,
    exhibition_id: 1,
    exhibition_name: "İstanbul Fuar Merkezi",
    type: "design",
    filename: "stand_tasarim_v1.pdf",
    uploaded_at: "2024-06-10T14:30:00Z",
    status: "approved"
  },
  {
    id: 2,
    exhibition_id: 1,
    exhibition_name: "İstanbul Fuar Merkezi",
    type: "technical_plan",
    filename: "teknik_plan.dwg",
    uploaded_at: "2024-06-15T10:20:00Z",
    status: "pending"
  },
  {
    id: 3,
    exhibition_id: 2,
    exhibition_name: "Ankara İş Fuarı",
    type: "safety_document",
    filename: "isg_belgesi.pdf",
    uploaded_at: "2024-07-05T16:45:00Z",
    status: "uploaded"
  }
];

// Mock API servisleri
export const mockExhibitionService = {
  getExhibitions: async (page = 1, limit = 10, search = ""): Promise<ExhibitionListResponse> => {
    await new Promise(resolve => setTimeout(resolve, 500)); // API gecikme simülasyonu
    
    let filteredData = mockExhibitions;
    if (search) {
      filteredData = mockExhibitions.filter(item => 
        item.name.toLowerCase().includes(search.toLowerCase()) ||
        `${item.responsible_person_name} ${item.responsible_person_surname}`.toLowerCase().includes(search.toLowerCase())
      );
    }
    
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    const paginatedData = filteredData.slice(startIndex, endIndex);
    
    return {
      status: true,
      message: "Fuar/Firma listesi başarıyla alındı",
      data: paginatedData,
      total: filteredData.length,
      page: page,
      limit: limit
    };
  },

  getExhibition: async (id: number): Promise<ExhibitionDetailResponse> => {
    await new Promise(resolve => setTimeout(resolve, 300));
    
    const exhibition = mockExhibitions.find(item => item.id === id);
    if (exhibition) {
      return {
        status: true,
        message: "Fuar/Firma detayı başarıyla alındı",
        data: exhibition
      };
    } else {
      return {
        status: false,
        message: "Fuar/Firma bulunamadı",
        data: {} as Exhibition
      };
    }
  },

  createExhibition: async (data: CreateExhibitionRequest): Promise<ExhibitionCreateResponse> => {
    await new Promise(resolve => setTimeout(resolve, 800));
    
    const newExhibition: Exhibition = {
      id: mockExhibitions.length + 1,
      name: data.name,
      responsible_person_name: data.responsible_person_name,
      responsible_person_surname: data.responsible_person_surname,
      responsible_person_email: data.responsible_person_email,
      status: "active",
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };
    
    mockExhibitions.push(newExhibition);
    
    return {
      status: true,
      message: "Fuar/Firma başarıyla oluşturuldu! Sorumlu kişiye e-posta gönderildi.",
      data: newExhibition
    };
  },

  updateExhibition: async (data: UpdateExhibitionRequest): Promise<ExhibitionUpdateResponse> => {
    await new Promise(resolve => setTimeout(resolve, 600));
    
    const index = mockExhibitions.findIndex(item => item.id === data.id);
    if (index !== -1) {
      mockExhibitions[index] = {
        ...mockExhibitions[index],
        ...data,
        updated_at: new Date().toISOString()
      };
      
      return {
        status: true,
        message: "Fuar/Firma başarıyla güncellendi",
        data: mockExhibitions[index]
      };
    } else {
      return {
        status: false,
        message: "Fuar/Firma bulunamadı"
      };
    }
  },

  deleteExhibition: async (id: number): Promise<{ status: boolean; message: string }> => {
    await new Promise(resolve => setTimeout(resolve, 400));
    
    const index = mockExhibitions.findIndex(item => item.id === id);
    if (index !== -1) {
      mockExhibitions.splice(index, 1);
      return {
        status: true,
        message: "Fuar/Firma başarıyla silindi"
      };
    } else {
      return {
        status: false,
        message: "Fuar/Firma bulunamadı"
      };
    }
  }
};

export const mockParticipantService = {
  getParticipants: async (page = 1, limit = 10, exhibition_id?: number) => {
    await new Promise(resolve => setTimeout(resolve, 400));
    
    let filteredData = mockParticipants;
    if (exhibition_id) {
      filteredData = mockParticipants.filter(item => item.exhibition_id === exhibition_id);
    }
    
    return {
      status: true,
      message: "Katılımcı listesi başarıyla alındı",
      data: filteredData
    };
  }
};

export const mockDecoratorService = {
  getDecorators: async (page = 1, limit = 10, exhibition_id?: number) => {
    await new Promise(resolve => setTimeout(resolve, 400));
    
    let filteredData = mockDecorators;
    if (exhibition_id) {
      filteredData = mockDecorators.filter(item => item.exhibition_id === exhibition_id);
    }
    
    return {
      status: true,
      message: "Dekoratör listesi başarıyla alındı",
      data: filteredData
    };
  }
};

export const mockDocumentService = {
  getDocuments: async (page = 1, limit = 10, exhibition_id?: number) => {
    await new Promise(resolve => setTimeout(resolve, 400));
    
    let filteredData = mockDocuments;
    if (exhibition_id) {
      filteredData = mockDocuments.filter(item => item.exhibition_id === exhibition_id);
    }
    
    return {
      status: true,
      message: "Belge listesi başarıyla alındı",
      data: filteredData
    };
  }
};
