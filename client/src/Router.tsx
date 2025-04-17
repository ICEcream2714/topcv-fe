import React from "react";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import App from "./App";
import CreatePage from "./pages/CreatePage";
import ReadPage from "./pages/ReadPage";

const Router = () => {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<App />} />
        <Route path="/create" element={<CreatePage />} />
        <Route path="/read" element={<ReadPage />} />
      </Routes>
    </BrowserRouter>
  );
};

export default Router;
