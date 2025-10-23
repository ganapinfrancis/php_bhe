import React, { useState } from 'react';
import './App.css'; // ✅ Import external CSS

function App() {
  const [gpa, setGpa] = useState('');
  const [examResults, setExamResults] = useState('');
  const [result, setResult] = useState(null);
  const [probability, setProbability] = useState(null);
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setResult(null);
    setProbability(null);
    setError('');

    try {
      const response = await fetch('http://127.0.0.1:5000/predict', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          gpa: parseFloat(gpa),
          exam_results: parseFloat(examResults)
        }),
      });

      const data = await response.json();
      if (response.ok) {
        setResult(data.result);
        setProbability(data.probability);
      } else {
        setError(data.error || 'Something went wrong.');
      }
    } catch (err) {
      setError('Failed to connect to server.');
    }
  };

  return (
    <div className="container">
      <h1 className="title">Scholarship Eligibility Checker</h1>
      <form onSubmit={handleSubmit} className="form">
        <label className="label">GPA</label>
        <input
          type="number"
          step="0.01"
          min="1"
          max="5"
          value={gpa}
          onChange={(e) => setGpa(e.target.value)}
          required
          className="input"
        />

        <label className="label">Exam Results</label>
        <input
          type="number"
          min="0"
          max="100"
          value={examResults}
          onChange={(e) => setExamResults(e.target.value)}
          required
          className="input"
        />

        <button type="submit" className="button">Check Eligibility</button>
      </form>

      {result && (
        <div className="result-box">
          <h2>{result}</h2>
          <p>Probability: {probability}</p>
        </div>
      )}

      {error && <p className="error">{error}</p>}
    </div>
  );
}

export default App;
