import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Form, Input, Button, Card, message, Row, Col } from "antd";
import { FiSave, FiArrowLeft, FiMail, FiUser, FiUsers } from "react-icons/fi";
import { exhibitionService } from "../../services/exhibitionApi";
import type { CreateExhibitionRequest } from "../../types/exhibition";
import "./css/ExhibitionForm.css";

const AddExhibition = () => {
  const [form] = Form.useForm();
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = async (values: CreateExhibitionRequest) => {
    setLoading(true);
    try {
      const response = await exhibitionService.createExhibition(values);
      if (response.status) {
        message.success(response.message || "Fuar/Firma başarıyla oluşturuldu!");
        form.resetFields();
        navigate("/exhibitions");
      } else {
        message.error(response.message);
      }
    } catch (error) {
      message.error("Fuar/Firma oluşturulurken hata oluştu.");
    } finally {
      setLoading(false);
    }
  };

  const handleGoBack = () => {
    navigate("/exhibitions");
  };

  return (
    <div className="exhibition-form-container">
      <div className="form-header">
        <Button
          type="text"
          icon={<FiArrowLeft />}
          onClick={handleGoBack}
          className="back-button"
        >
          Geri Dön
        </Button>
        <div className="form-title-section">
          <h1 className="form-title">Yeni Fuar/Firma Ekle</h1>
          <p className="form-description">
            Sisteme yeni bir fuar veya firma ekleyin. Sorumlu kişiye otomatik e-posta gönderilecektir.
          </p>
        </div>
      </div>

      <Card className="form-card">
        <Form
          form={form}
          layout="vertical"
          onFinish={handleSubmit}
          className="exhibition-form"
          size="large"
        >
          <Row gutter={24}>
            <Col xs={24} lg={12}>
              <Form.Item
                label={
                  <span className="form-label">
                    <FiUsers className="label-icon" />
                    Fuar/Firma Adı
                  </span>
                }
                name="name"
                rules={[
                  { required: true, message: "Fuar/Firma adı gereklidir" },
                  { min: 2, message: "En az 2 karakter olmalıdır" },
                  { max: 100, message: "En fazla 100 karakter olabilir" }
                ]}
              >
                <Input
                  placeholder="Örn: İstanbul Fuar Merkezi veya ABC Şirketi"
                  className="form-input"
                />
              </Form.Item>
            </Col>

            <Col xs={24} lg={12}>
              <Form.Item
                label={
                  <span className="form-label">
                    <FiMail className="label-icon" />
                    Sorumlu Kişi E-posta
                  </span>
                }
                name="responsible_person_email"
                rules={[
                  { required: true, message: "E-posta adresi gereklidir" },
                  { type: "email", message: "Geçerli bir e-posta adresi girin" }
                ]}
              >
                <Input
                  placeholder="ornek@firma.com"
                  className="form-input"
                />
              </Form.Item>
            </Col>

            <Col xs={24} lg={12}>
              <Form.Item
                label={
                  <span className="form-label">
                    <FiUser className="label-icon" />
                    Sorumlu Kişi Adı
                  </span>
                }
                name="responsible_person_name"
                rules={[
                  { required: true, message: "Sorumlu kişi adı gereklidir" },
                  { min: 2, message: "En az 2 karakter olmalıdır" },
                  { max: 50, message: "En fazla 50 karakter olabilir" }
                ]}
              >
                <Input
                  placeholder="Adı"
                  className="form-input"
                />
              </Form.Item>
            </Col>

            <Col xs={24} lg={12}>
              <Form.Item
                label={
                  <span className="form-label">
                    <FiUser className="label-icon" />
                    Sorumlu Kişi Soyadı
                  </span>
                }
                name="responsible_person_surname"
                rules={[
                  { required: true, message: "Sorumlu kişi soyadı gereklidir" },
                  { min: 2, message: "En az 2 karakter olmalıdır" },
                  { max: 50, message: "En fazla 50 karakter olabilir" }
                ]}
              >
                <Input
                  placeholder="Soyadı"
                  className="form-input"
                />
              </Form.Item>
            </Col>
          </Row>

          <div className="form-actions">
            <Button
              type="default"
              size="large"
              onClick={handleGoBack}
              className="cancel-button"
            >
              İptal
            </Button>
            <Button
              type="primary"
              size="large"
              htmlType="submit"
              loading={loading}
              icon={<FiSave />}
              className="submit-button"
            >
              {loading ? "Kaydediliyor..." : "Kaydet"}
            </Button>
          </div>
        </Form>
      </Card>

      <div className="info-section">
        <Card className="info-card">
          <h3 className="info-title">📧 Otomatik E-posta Bildirimi</h3>
          <p className="info-text">
            Fuar/Firma oluşturulduktan sonra, sorumlu kişiye otomatik olarak 
            giriş bilgileri ve sistem kullanım talimatları e-posta ile gönderilecektir.
          </p>
        </Card>
      </div>
    </div>
  );
};

export default AddExhibition;
