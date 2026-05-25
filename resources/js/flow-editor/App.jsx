import React, { useState, useCallback, useEffect, useRef } from 'react';
import {
  ReactFlow,
  addEdge,
  Background,
  Controls,
  MiniMap,
  Panel,
  useNodesState,
  useEdgesState,
  applyEdgeChanges,
  applyNodeChanges,
  Handle,
  Position,
} from '@xyflow/react';
import '@xyflow/react/dist/style.css';
import axios from 'axios';

// Custom Node Components
const BaseNode = ({ label, children, color }) => (
  <div style={{
    padding: '10px',
    borderRadius: '5px',
    background: '#fff',
    border: `2px solid ${color}`,
    minWidth: '150px',
    boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
  }}>
    <Handle type="target" position={Position.Top} />
    <div style={{ fontWeight: 'bold', marginBottom: '5px', borderBottom: '1px solid #eee' }}>{label}</div>
    <div style={{ fontSize: '12px' }}>{children}</div>
    <Handle type="source" position={Position.Bottom} />
  </div>
);

const ModelNodeComponent = ({ data }) => (
  <BaseNode label="Model" color="#3b82f6">
    <div>{data.model_name || 'Select a model...'}</div>
  </BaseNode>
);

const StartNodeComponent = ({ data }) => (
  <div style={{
    padding: '15px',
    borderRadius: '50%',
    background: '#fff',
    border: `3px solid #22c55e`,
    width: '60px',
    height: '60px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontWeight: 'bold',
    boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
    fontSize: '12px'
  }}>
    START
    <Handle type="source" position={Position.Bottom} />
  </div>
);

const EndNodeComponent = ({ data }) => (
  <div style={{
    padding: '15px',
    borderRadius: '50%',
    background: '#fff',
    border: `3px solid #ef4444`,
    width: '60px',
    height: '60px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontWeight: 'bold',
    boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
    fontSize: '12px'
  }}>
    <Handle type="target" position={Position.Top} />
    END
  </div>
);

const ParallelNodeComponent = ({ data }) => (
  <BaseNode label="Parallel" color="#a855f7">
    <div>Parallel execution</div>
  </BaseNode>
);

const AggregatorNodeComponent = ({ data }) => (
  <BaseNode label="Aggregator" color="#22c55e">
    <div>Strategy: {data.aggregation_strategy || 'Default'}</div>
  </BaseNode>
);

const LoopNodeComponent = ({ data }) => (
  <BaseNode label="Loop" color="#f97316">
    <div>Max iterations: {data.max_iterations || 1}</div>
  </BaseNode>
);

const ConditionNodeComponent = ({ data }) => (
  <BaseNode label="Condition" color="#eab308">
    <div>{data.expression ? `If: ${data.expression.substring(0, 20)}...` : 'No expression'}</div>
  </BaseNode>
);

const RouterNodeComponent = ({ data }) => (
  <BaseNode label="Router" color="#ef4444">
    <div>Routing logic</div>
  </BaseNode>
);

const DataProcessorNodeComponent = ({ data }) => (
  <BaseNode label="Data Processor" color="#6b7280">
    <div>{data.script ? 'Has script' : 'No script'}</div>
  </BaseNode>
);

const nodeTypes = {
  start: StartNodeComponent,
  end: EndNodeComponent,
  model: ModelNodeComponent,
  parallel: ParallelNodeComponent,
  aggregator: AggregatorNodeComponent,
  loop: LoopNodeComponent,
  condition: ConditionNodeComponent,
  router: RouterNodeComponent,
  dataProcessor: DataProcessorNodeComponent,
};

const paletteItems = [
  { type: 'start', label: 'Start', color: '#22c55e' },
  { type: 'end', label: 'End', color: '#ef4444' },
  { type: 'model', label: 'Model', color: '#3b82f6' },
  { type: 'parallel', label: 'Parallel', color: '#a855f7' },
  { type: 'aggregator', label: 'Aggregator', color: '#22c55e' },
  { type: 'loop', label: 'Loop', color: '#f97316' },
  { type: 'condition', label: 'Condition', color: '#eab308' },
  { type: 'router', label: 'Router', color: '#ef4444' },
  { type: 'dataProcessor', label: 'Data Processor', color: '#6b7280' },
];

export default function App() {
  const [nodes, setNodes, onNodesChange] = useNodesState([]);
  const [edges, setEdges, onEdgesChange] = useEdgesState([]);
  const [selectedNode, setSelectedNode] = useState(null);
  const [models, setModels] = useState([]);
  const [loading, setLoading] = useState(true);
  const moduleId = document.getElementById('flow-editor-root')?.dataset.moduleId;

  useEffect(() => {
    if (!moduleId) return;

    const fetchData = async () => {
      try {
        const [flowRes, modelsRes] = await Promise.all([
          axios.get(`/api/flow-editor/${moduleId}`),
          axios.get(`/api/flow-editor/${moduleId}/models`)
        ]);
        setNodes(flowRes.data.flow_json.nodes || []);
        setEdges(flowRes.data.flow_json.edges || []);
        setModels(modelsRes.data);
      } catch (error) {
        console.error('Error loading flow data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [moduleId]);

  const onConnect = useCallback((params) => setEdges((eds) => addEdge(params, eds)), []);

  const onDragStart = (event, nodeType) => {
    event.dataTransfer.setData('application/reactflow', nodeType);
    event.dataTransfer.effectAllowed = 'move';
  };

  const onDrop = useCallback(
    (event) => {
      event.preventDefault();
      const type = event.dataTransfer.getData('application/reactflow');
      if (!type) return;

      const position = { x: event.clientX - 400, y: event.clientY - 40 };
      const newNode = {
        id: Math.random().toString(36).substr(2, 9),
        type,
        position,
        data: { 
          label: `${type} node`,
          // Default config for new nodes
          ...(type === 'model' ? { provider_model_id: '', system_prompt: '' } : {}),
          ...(type === 'end' ? { output_format: 'text' } : {}),
        },
      };

      setNodes((nds) => nds.concat(newNode));
    },
    [setNodes]
  );

  const onDragOver = useCallback((event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
  }, []);

  const onNodeDoubleClick = useCallback((event, node) => {
    setSelectedNode(node);
  }, []);

  const updateNodeData = (id, newData) => {
    setNodes((nds) =>
      nds.map((node) => {
        if (node.id === id) {
          return { ...node, data: { ...node.data, ...newData } };
        }
        return node;
      })
    );
    setSelectedNode((prev) => prev && prev.id === id ? { ...prev, data: { ...prev.data, ...newData } } : prev);
  };

  const saveFlow = async () => {
    try {
      await axios.put(`/api/flow-editor/${moduleId}`, {
        flow_json: { nodes, edges }
      });
      alert('Saved successfully!');
    } catch (error) {
      console.error('Error saving flow:', error);
      alert('Save failed.');
    }
  };

  if (loading) return <div style={{ padding: '20px' }}>Loading flow editor...</div>;

  return (
    <div style={{ display: 'flex', width: '100vw', height: '100vh', background: '#f8fafc' }}>
      {/* Selection Palette */}
      <div style={{ width: '250px', borderRight: '1px solid #e2e8f0', padding: '15px', background: '#fff' }}>
        <h3 style={{ marginBottom: '15px', fontWeight: 'bold' }}>Nodes</h3>
        {paletteItems.map((item) => (
          <div
            key={item.type}
            onDragStart={(event) => onDragStart(event, item.type)}
            draggable
            style={{
              padding: '10px',
              marginBottom: '10px',
              border: `1px solid ${item.color}`,
              borderRadius: '4px',
              cursor: 'grab',
              background: '#fff',
            }}
          >
            {item.label}
          </div>
        ))}
        <button
          onClick={saveFlow}
          style={{
            marginTop: '20px',
            width: '100%',
            padding: '10px',
            background: '#3b82f6',
            color: '#fff',
            borderRadius: '4px',
            border: 'none',
            cursor: 'pointer',
          }}
        >
          Save Changes
        </button>
        <button
          onClick={() => {
            window.location.href = `/admin/composite-modules/${moduleId}/edit`;
          }}
          style={{
            marginTop: '10px',
            width: '100%',
            padding: '10px',
            background: '#64748b',
            color: '#fff',
            borderRadius: '4px',
            border: 'none',
            cursor: 'pointer',
          }}
        >
          Close
        </button>
      </div>

      {/* Flow Canvas */}
      <div style={{ flexGrow: 1, height: '100%' }}>
        <ReactFlow
          nodes={nodes}
          edges={edges}
          onNodesChange={onNodesChange}
          onEdgesChange={onEdgesChange}
          onConnect={onConnect}
          onDrop={onDrop}
          onDragOver={onDragOver}
          onNodeDoubleClick={onNodeDoubleClick}
          nodeTypes={nodeTypes}
          fitView
        >
          <Background />
          <Controls />
          <MiniMap />
        </ReactFlow>
      </div>

      {/* Settings Panel */}
      {selectedNode && (
        <div style={{ width: '350px', borderLeft: '1px solid #e2e8f0', padding: '15px', background: '#fff', overflowY: 'auto' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '15px' }}>
            <h3 style={{ fontWeight: 'bold' }}>Settings: {selectedNode.type}</h3>
            <button onClick={() => setSelectedNode(null)}>Close</button>
          </div>
          
          <div style={{ display: 'flex', flexDirection: 'column', gap: '15px' }}>
            {selectedNode.type === 'model' && (
              <>
                <label>
                  Model:
                  <select
                    value={selectedNode.data.provider_model_id || ''}
                    onChange={(e) => {
                      const model = models.find(m => m.id == e.target.value);
                      updateNodeData(selectedNode.id, {
                        provider_model_id: e.target.value,
                        model_name: model ? `${model.provider_name}: ${model.name}` : ''
                      });
                    }}
                    style={{ width: '100%', padding: '5px' }}
                  >
                    <option value="">Select a model</option>
                    {models.map(m => <option key={m.id} value={m.id}>{m.provider_name}: {m.name}</option>)}
                  </select>
                </label>
                <label>
                  System Prompt:
                  <textarea
                    value={selectedNode.data.system_prompt || ''}
                    onChange={(e) => updateNodeData(selectedNode.id, { system_prompt: e.target.value })}
                    style={{ width: '100%', height: '100px', padding: '5px' }}
                  />
                </label>
                <label>
                  Fallback Model:
                  <select
                    value={selectedNode.data.fallback_model_id || ''}
                    onChange={(e) => updateNodeData(selectedNode.id, { fallback_model_id: e.target.value })}
                    style={{ width: '100%', padding: '5px' }}
                  >
                    <option value="">None</option>
                    {models.map(m => <option key={m.id} value={m.id}>{m.provider_name}: {m.name}</option>)}
                  </select>
                </label>
              </>
            )}

            {selectedNode.type === 'end' && (
              <>
                <label>
                  Output Format:
                  <select
                    value={selectedNode.data.output_format || 'text'}
                    onChange={(e) => updateNodeData(selectedNode.id, { output_format: e.target.value })}
                    style={{ width: '100%', padding: '5px' }}
                  >
                    <option value="text">Text</option>
                    <option value="json">JSON</option>
                    <option value="markdown">Markdown</option>
                  </select>
                </label>
              </>
            )}

            {selectedNode.type === 'aggregator' && (
              <>
                <label>
                  Timeout (ms):
                  <input
                    type="number"
                    value={selectedNode.data.timeout_ms || 30000}
                    onChange={(e) => updateNodeData(selectedNode.id, { timeout_ms: e.target.value })}
                    style={{ width: '100%', padding: '5px' }}
                  />
                </label>
                <label>
                  Strategy:
                  <select
                    value={selectedNode.data.aggregation_strategy || 'concat'}
                    onChange={(e) => updateNodeData(selectedNode.id, { aggregation_strategy: e.target.value })}
                    style={{ width: '100%', padding: '5px' }}
                  >
                    <option value="concat">Concatenate</option>
                    <option value="json_merge">JSON Merge</option>
                    <option value="most_frequent">Most Frequent</option>
                  </select>
                </label>
              </>
            )}

            {selectedNode.type === 'loop' && (
              <>
                <label>
                  Max Iterations:
                  <input
                    type="number"
                    value={selectedNode.data.max_iterations || 1}
                    onChange={(e) => updateNodeData(selectedNode.id, { max_iterations: e.target.value })}
                    style={{ width: '100%', padding: '5px' }}
                  />
                </label>
                <label>
                  Break Condition:
                  <textarea
                    value={selectedNode.data.break_condition || ''}
                    onChange={(e) => updateNodeData(selectedNode.id, { break_condition: e.target.value })}
                    style={{ width: '100%', height: '80px', padding: '5px' }}
                    placeholder="PHP expression"
                  />
                </label>
              </>
            )}

            {selectedNode.type === 'condition' && (
              <label>
                Expression:
                <textarea
                  value={selectedNode.data.expression || ''}
                  onChange={(e) => updateNodeData(selectedNode.id, { expression: e.target.value })}
                  style={{ width: '100%', height: '80px', padding: '5px' }}
                  placeholder="PHP expression"
                />
              </label>
            )}

            {selectedNode.type === 'dataProcessor' && (
              <label>
                Script:
                <textarea
                  value={selectedNode.data.script || ''}
                  onChange={(e) => updateNodeData(selectedNode.id, { script: e.target.value })}
                  style={{ width: '100%', height: '150px', padding: '5px' }}
                  placeholder="PHP script content"
                />
              </label>
            )}
          </div>
          
          <div style={{ marginTop: '20px', borderTop: '1px solid #eee', paddingTop: '15px' }}>
             <button
              onClick={() => {
                setNodes(nds => nds.filter(n => n.id !== selectedNode.id));
                setSelectedNode(null);
              }}
              style={{ background: '#ef4444', color: '#fff', border: 'none', padding: '5px 10px', borderRadius: '4px', cursor: 'pointer' }}
            >
              Delete Node
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
