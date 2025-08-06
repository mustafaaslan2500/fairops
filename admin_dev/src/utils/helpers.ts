export const handleApiError = (error: any): string => {
  if (error.response?.data?.message) {
    return error.response.data.message;
  }
  
  if (error.message) {
    return error.message;
  }
  
  return "Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.";
};

export const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

export const formatUserName = (name?: string, surname?: string): string => {
  if (!name && !surname) return "Kullanıcı";
  return `${name || ""} ${surname || ""}`.trim();
};
