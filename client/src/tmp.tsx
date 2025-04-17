import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import "./App.less";
import { Layout, Button, Card, List, Tag, Typography, Space, Flex, theme, Row, Col } from "antd";
import { PlusOutlined, CalendarOutlined, EnvironmentOutlined } from '@ant-design/icons';

const { Header, Footer, Content } = Layout;
const { Title, Paragraph, Text } = Typography;

// Interface cho dữ liệu Job
interface Job {
  id: number | string;
  positionName: string;
  companyName: string;
  companyLocation: string;
  description: string;
  applicationButton: string;
  deadline: string;
}

const App = () => {
  const navigate = useNavigate();
  const [jobs, setJobs] = useState<Job[]>([]);
  const [loading, setLoading] = useState(true);

  const {
    token: { colorBgContainer, borderRadiusLG },
  } = theme.useToken();

  // Fetch dữ liệu từ JSON Server
  useEffect(() => {
    const fetchJobs = async () => {
      try {
        const response = await fetch('http://localhost:3001/jobs');
        const data = await response.json();
        setJobs(data);
        setLoading(false);
      } catch (error) {
        console.error('Error fetching jobs:', error);
        setLoading(false);
      }
    };

    fetchJobs();
  }, []);

  // Hàm định dạng ngày tháng
  const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('vi-VN', options);
  };

  // Chuyển đến trang tạo mới
  const handleCreateJob = () => {
    navigate('/create');
  };

  // Chuyển đến trang chi tiết/cập nhật
  const handleViewJob = (id: number | string) => {
    navigate(`/read?id=${id}`);
  };

  return (
    <div className="App">
      <Layout>
        <Header style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
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
          <div className="content-container"
            style={{
              background: colorBgContainer,
              padding: 24,
              borderRadius: borderRadiusLG,
              marginTop: 16
            }}
          >
            <Row gutter={[16, 16]}>
              {loading ? (
                <div>Đang tải dữ liệu...</div>
              ) : (
                jobs.map(job => (
                  <Col xs={24} sm={12} md={8} key={job.id}>
                    <Card 
                      hoverable
                      title={job.positionName}
                      extra={<Tag color="blue">{formatDate(job.deadline)}</Tag>}
                      onClick={() => handleViewJob(job.id)}
                    >
                      <Space direction="vertical" size="small" style={{ width: '100%' }}>
                        <Title level={5}>{job.companyName}</Title>
                        <Text type="secondary">
                          <EnvironmentOutlined /> {job.companyLocation}
                        </Text>
                        <Paragraph ellipsis={{ rows: 2 }}>
                          {job.description}
                        </Paragraph>
                        <Flex justify="space-between" align="center">
                          <Text type="secondary">
                            <CalendarOutlined /> Hạn chót: {formatDate(job.deadline)}
                          </Text>
                          <Button type="primary">{job.applicationButton}</Button>
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
    </div>
  );
};

export default App;
