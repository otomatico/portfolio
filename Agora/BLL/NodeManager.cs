using Agora.DAL.Data;
using Agora.Models;
namespace Agora.BLL
{
    public interface INodeManager
    {
        public Task<List<NodeRoot>> GetNodeRoot();
        public Task<List<NodeList>> GetNodesList(Guid IdNodeRoot);
        public Task RemoveNodeRoot(Guid IdNode);
        public Task SetNodeRoot(Node node);
        public Task SetNode(NodeList node);
        public Task RemoveNode(Guid IdNode);

    }

    public class NodeManager : INodeManager
    {
        private readonly INodeData _nodeData;
        private readonly INodeRelationData _nodeRelationData;
        public NodeManager(INodeData nodeData, INodeRelationData nodeRelationData)
        {
            _nodeData = nodeData;
            _nodeRelationData = nodeRelationData;
        }
        public async Task<List<NodeRoot>> GetNodeRoot()
        {
            return await _nodeData.GetRootAsync();
        }
        public async Task<List<NodeList>> GetNodesList(Guid IdNodeRoot)
        {
            return await _nodeData.GetNodesListAsync(IdNodeRoot);
        }
        public async Task RemoveNodeRoot(Guid IdNode)
        {
            List<NodeRelation> relations = await _nodeRelationData.GetNodeRelationByIdNodeRootAsync(IdNode);
            await _nodeRelationData.DeleteNodeRelationByIdNodeRootAsync(IdNode);
            await _nodeData.DeleteNodesAsync(relations.Select(el => el.IdNode).ToArray());
            await _nodeData.DeleteNodeAsync(IdNode);
        }
        public async Task SetNodeRoot(Node node)
        {
            node.ModifyDate = DateTime.UtcNow;
            if (node.IdNode != null)
            {
                await _nodeData.UpdateNodeAsync(node);
            }
            else
            {
                await _nodeData.InsertNodeAsync(node);
            }
        }
        public async Task SetNode(NodeList node)
        {
            if (node.IdNode != null)
            {
                await _nodeData.UpdateNodeAsync(node);
            }
            else
            {
                await _nodeData.InsertNodeAsync(node);
                NodeRelation? relation = await _nodeRelationData.GetNodeRelationByIdNodeAsync(node.IdNodeParent ?? Guid.Empty);
                if (relation != null)
                {
                    relation.IdNode = node.IdNode ?? Guid.Empty;
                }
                else
                {
                    relation = new NodeRelation()
                    {
                        IdNode = node.IdNode ?? Guid.Empty,
                        IdNodeParent = node.IdNodeParent ?? Guid.Empty,
                        IdNodeRoot = node.IdNodeParent ?? Guid.Empty,
                    };
                }
                await _nodeRelationData.InsertNodeRelationAsync(relation);

            }
        }
        public async Task RemoveNode(Guid IdNode)
        {
            List<NodeRelation> relations = await _nodeRelationData.GetNodeRelationByIdNodeParentAsync(IdNode);

            if (relations.Count > 0)
            {
                Guid[] nodeChildrens = relations.Select(p => p.IdNodeParent).Distinct().ToArray();
                foreach (var el in nodeChildrens)
                {
                    await _nodeRelationData.DeleteNodeRelationAsync(el);
                }
                await _nodeData.DeleteNodesAsync(relations.Select(el => el.IdNode).ToArray());
            }
            await _nodeRelationData.DeleteNodeRelationAsync(IdNode);
            await _nodeData.DeleteNodeAsync(IdNode);
        }
    }
}