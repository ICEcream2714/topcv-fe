import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import "./App.less";
import {
  Layout,
  Flex,
  Button,
  theme,
  Typography,
  Row,
  Col,
  Card,
  Tag,
  Space,
  message,
  Popconfirm,
} from "antd";
import {
  PlusOutlined,
  CalendarOutlined,
  EnvironmentOutlined,
  EditOutlined,
  DeleteOutlined,
} from "@ant-design/icons";
import JobFormModal from "./components/JobFormModal";
import { API_ENDPOINTS } from "./config/api";

const { Header, Footer, Content } = Layout;
const { Title, Paragraph, Text } = Typography;

interface Job {
  id?: number | string;
  positionName: string;
  companyName: string;
  companyLocation: string;
  description: string;
  applicationButton: string;
  deadline: string;
}

const App = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [jobs, setJobs] = useState<Job[]>([]);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [isEditModalVisible, setIsEditModalVisible] = useState(false);
  const [selectedJob, setSelectedJob] = useState<Job | undefined>(undefined);

  const {
    token: { colorBgContainer, borderRadiusLG },
  } = theme.useToken();

  // Hàm định dạng ngày tháng
  const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = {
      year: "numeric",
      month: "long",
      day: "numeric",
    };
    return new Date(dateString).toLocaleDateString("vi-VN", options);
  };

  const handleCreateJob = () => {
    setIsModalVisible(true);
  };

  const handleViewJob = (id: number | string) => {
    navigate(`/read?id=${id}`);
  };

  const handleCancel = () => {
    setIsModalVisible(false);
  };

  const handleEditCancel = () => {
    setIsEditModalVisible(false);
    setSelectedJob(undefined);
  };

  const handleEditJob = (e: React.MouseEvent, job: Job) => {
    e.stopPropagation(); // Ngăn sự kiện click lan tỏa lên card
    setSelectedJob(job);
    setIsEditModalVisible(true);
  };

  const handleDeleteJob = async (
    e: React.MouseEvent,
    jobId: number | string | undefined
  ) => {
    e.stopPropagation(); // Ngăn sự kiện click lan tỏa lên card
    if (!jobId) return;

    try {
      const response = await fetch(`${API_ENDPOINTS.JOBS}/${jobId}`, {
        method: "DELETE",
      });

      if (response.ok) {
        message.success("Công việc đã được xóa thành công!");
        setJobs((prevJobs) => prevJobs.filter((job) => job.id !== jobId));
      } else {
        message.error("Có lỗi xảy ra khi xóa công việc!");
      }
    } catch (error) {
      console.error("Error deleting job:", error);
      message.error("Có lỗi xảy ra khi xóa công việc!");
    }
  };

  const handleSubmitSuccess = (newJob: Job) => {
    setIsModalVisible(false);
    setJobs((prevJobs) => [...prevJobs, newJob]);
  };

  const handleUpdateSuccess = (updatedJob: Job) => {
    setIsEditModalVisible(false);
    setSelectedJob(undefined);
    setJobs((prevJobs) =>
      prevJobs.map((job) => (job.id === updatedJob.id ? updatedJob : job))
    );
  };

  useEffect(() => {
    const fetchJobs = async () => {
      try {
        const response = await fetch(API_ENDPOINTS.JOBS);
        const data = await response.json();
        setJobs(data);
        setLoading(false);
      } catch (error) {
        console.error("Error fetching jobs:", error);
        setLoading(false);
      }
    };

    fetchJobs();
  }, []);

  return (
    <div className="App">
      <Layout>
        <Header
          style={{
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
          }}
        >
          <h1 className="header-title">Ứng dụng CRUD Job đơn giản</h1>
          <Button
            type="primary"
            icon={<PlusOutlined />}
            onClick={handleCreateJob}
          >
            Thêm công việc mới
          </Button>
        </Header>
        <Content style={{ padding: "0 48px" }}>
          <div
            className="content-container"
            style={{
              background: colorBgContainer,
              padding: 24,
              borderRadius: borderRadiusLG,
            }}
          >
            <Row gutter={[16, 16]}>
              {loading ? (
                <div>Loading...</div>
              ) : (
                jobs.map((job) => (
                  <Col span={24} key={job.id}>
                    <Card
                      hoverable
                      title={job.positionName}
                      extra={<Tag color="blue">{formatDate(job.deadline)}</Tag>}
                      onClick={() => handleViewJob(job.id)}
                    >
                      <Space
                        direction="vertical"
                        size="small"
                        style={{ width: "100%" }}
                      >
                        <Title level={5}>{job.companyName}</Title>
                        <Text type="secondary">
                          <EnvironmentOutlined /> {job.companyLocation}
                        </Text>
                        <Paragraph ellipsis={{ rows: 2 }}>
                          {job.description}
                        </Paragraph>
                        <Flex justify="space-between" align="center">
                          <Text type="secondary">
                            <CalendarOutlined /> Hạn chót:{" "}
                            {formatDate(job.deadline)}
                          </Text>
                          <Space direction="vertical" align="end">
                            <Space>
                              <Button
                                type="default"
                                icon={<EditOutlined />}
                                onClick={(e) => handleEditJob(e, job)}
                              >
                                Sửa
                              </Button>
                              <Popconfirm
                                title="Bạn có chắc chắn muốn xóa công việc này?"
                                onConfirm={(e) => handleDeleteJob(e!, job.id)}
                                okText="Có"
                                cancelText="Không"
                                onCancel={(e) => e?.stopPropagation()}
                              >
                                <Button
                                  type="default"
                                  danger
                                  icon={<DeleteOutlined />}
                                  onClick={(e) => e.stopPropagation()}
                                >
                                  Xóa
                                </Button>
                              </Popconfirm>
                            </Space>
                            <Button
                              type="primary"
                              style={{ minWidth: "150px" }}
                              onClick={(e) => e.stopPropagation()}
                            >
                              {job.applicationButton || "Apply Now"}
                            </Button>
                          </Space>
                        </Flex>
                      </Space>
                    </Card>
                  </Col>
                ))
              )}
            </Row>
          </div>
        </Content>
        <Footer style={{ textAlign: "center" }}>
          Ant Design ©{new Date().getFullYear()} Created by Ant UED
        </Footer>
      </Layout>
      <JobFormModal
        isVisible={isModalVisible}
        onCancel={handleCancel}
        onSubmitSuccess={handleSubmitSuccess}
        isEditing={false}
      />
      {isEditModalVisible && selectedJob && (
        <JobFormModal
          isVisible={isEditModalVisible}
          onCancel={handleEditCancel}
          onSubmitSuccess={handleUpdateSuccess}
          job={selectedJob}
          isEditing={true}
        />
      )}
    </div>
  );
};

export default App;
