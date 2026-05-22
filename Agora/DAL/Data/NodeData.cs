using Agora.DAL.Query;
using Agora.Models;
using Microsoft.Extensions.Configuration;
using Dapper;
using System.Data;

//using System.Text.Json;

namespace Agora.DAL.Data
{
    public interface INodeData
    {
        public Task<List<NodeRoot>> GetRootAsync();
        public Task<List<NodeList>> GetNodesListAsync(Guid IdNodeRoot);
        public Task InsertNodeAsync(Node node);
        public Task UpdateNodeAsync(Node node);
        public Task DeleteNodeAsync(Guid IdNode);
        public Task DeleteNodesAsync(Guid[] IdNode);

    }
    public class NodeData : INodeData
    {
        private readonly IDbConnection _connection;
        public NodeData(IConfiguration configuration, IDbConnection connection)
        {
            _connection = connection;
            _connection.ConnectionString = configuration.GetConnectionString("dbSQL");
        }

        #region NodeRoot
        public async Task<List<NodeRoot>> GetRootAsync()
        {
            List<NodeRoot> result;
            try
            {
                _connection.Open();
                result = (await _connection.QueryAsync<NodeRoot>(NodeQueries.GetNodeRoot)).ToList();
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD NodeData.GetRootAsync:", ex);
            }
            return result;
        }
        public async Task<List<NodeList>> GetNodesListAsync(Guid IdNodeRoot)
        {
            List<NodeList> result;
            try
            {
                _connection.Open();
                result = (await _connection.QueryAsync<NodeList>(NodeQueries.GetNodesListByIdNodeRoot, new { IdNode = IdNodeRoot })).ToList();
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD NodeData.GetNodesListAsync:", ex);
            }
            return result;
        }
        public async Task InsertNodeAsync(Node node)
        {
            try
            {
                node.IdNode = Guid.NewGuid();
                _connection.Open();
                await _connection.ExecuteAsync(NodeQueries.InsertNode, node);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD NodeData.InsertNodeAsync:", ex);
            }
        }

        public async Task UpdateNodeAsync(Node node)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(NodeQueries.UpdateNode, node);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD NodeData.UpdateNodeAsync:", ex);
            }
        }

        public async Task DeleteNodeAsync(Guid IdNode)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(NodeQueries.DeleteNode, new { IdNode });
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD NodeData.DeleteNodeAsync:", ex);
            }
        }
        public async Task DeleteNodesAsync(Guid[] IdNodes)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(NodeQueries.DeleteNodes, new { IdNodes });
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD NodeData.DeleteNodeAsync:", ex);
            }
        }
        #endregion

        #region Node

        #endregion
    }
}
