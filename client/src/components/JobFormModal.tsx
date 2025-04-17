import React, { useEffect } from "react";
import { Modal, Form, Input, DatePicker, message } from "antd";
import dayjs from "dayjs";

const { TextArea } = Input;

interface Job {
  id?: number | string;
  positionName: string;
  companyName: string;
  companyLocation: string;
  description: string;
  applicationButton: string;
  deadline: string;
}

interface JobFormModalProps {
  isVisible: boolean;
  onCancel: () => void;
  onSubmitSuccess: (job: Job) => void;
  job?: Job; // Dữ liệu job cho chức năng edit, undefined cho chức năng thêm mới
  isEditing?: boolean; // Flag để biết đang trong chế độ chỉnh sửa hay thêm mới
}

const JobFormModal: React.FC<JobFormModalProps> = ({
  isVisible,
  onCancel,
  onSubmitSuccess,
  job,
  isEditing = false,
}) => {
  const [form] = Form.useForm();

  // Reset form và điền dữ liệu nếu trong chế độ edit
  useEffect(() => {
    if (isVisible) {
      form.resetFields();

      if (isEditing && job) {
        form.setFieldsValue({
          ...job,
          deadline: job.deadline ? dayjs(job.deadline) : undefined,
        });
      }
    }
  }, [isVisible, job, isEditing, form]);

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields();

      // Chuyển đổi giá trị ngày từ dayjs sang chuỗi format ISO
      const formattedValues = {
        ...values,
        deadline: values.deadline ? values.deadline.format("YYYY-MM-DD") : "",
      };

      if (isEditing && job?.id) {
        // Cập nhật job nếu đang ở chế độ edit
        const response = await fetch(`http://localhost:3001/jobs/${job.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formattedValues),
        });

        if (response.ok) {
          message.success("Công việc đã được cập nhật thành công!");
          form.resetFields();
          const updatedJob = await response.json();
          onSubmitSuccess(updatedJob);
        } else {
          message.error("Có lỗi xảy ra khi cập nhật công việc!");
        }
      } else {
        // Thêm job mới nếu không phải chế độ edit
        const response = await fetch("http://localhost:3001/jobs", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formattedValues),
        });

        if (response.ok) {
          message.success("Công việc đã được thêm thành công!");
          form.resetFields();
          const newJob = await response.json();
          onSubmitSuccess(newJob);
        } else {
          message.error("Có lỗi xảy ra khi thêm công việc!");
        }
      }
    } catch (error) {
      console.error("Error submitting form:", error);
      message.error("Có lỗi xảy ra khi xử lý biểu mẫu!");
    }
  };

  const handleCancel = () => {
    form.resetFields();
    onCancel();
  };

  return (
    <Modal
      title={isEditing ? "Chỉnh sửa công việc" : "Thêm công việc mới"}
      open={isVisible}
      onCancel={handleCancel}
      onOk={handleSubmit}
      okText={isEditing ? "Cập nhật" : "Thêm"}
      cancelText="Hủy"
    >
      <Form form={form} layout="vertical">
        <Form.Item
          name="positionName"
          label="Tên vị trí"
          rules={[{ required: true, message: "Vui lòng nhập tên vị trí!" }]}
        >
          <Input />
        </Form.Item>
        <Form.Item
          name="companyName"
          label="Tên công ty"
          rules={[{ required: true, message: "Vui lòng nhập tên công ty!" }]}
        >
          <Input />
        </Form.Item>
        <Form.Item
          name="companyLocation"
          label="Địa điểm công ty"
          rules={[
            { required: true, message: "Vui lòng nhập địa điểm công ty!" },
          ]}
        >
          <Input />
        </Form.Item>
        <Form.Item
          name="description"
          label="Mô tả"
          rules={[{ required: true, message: "Vui lòng nhập mô tả!" }]}
        >
          <TextArea rows={4} />
        </Form.Item>
        <Form.Item
          name="deadline"
          label="Hạn chót"
          rules={[{ required: true, message: "Vui lòng chọn hạn chót!" }]}
        >
          <DatePicker style={{ width: "100%" }} />
        </Form.Item>
      </Form>
    </Modal>
  );
};

export default JobFormModal;
