using Agora.DAL.Query;
using Agora.Models;
using Microsoft.Extensions.Configuration;
using Dapper;
using System.Data;

namespace Agora.DAL.Data
{
    public interface ITemplateAllowedChildrenData
    {
        public Task<List<TemplateAllowedChildren>> GetChildrensAsync(Guid IdTemplateParent);
        public Task<List<TemplateAllowedChildren>> GetChildrenByIdNodeAsync(Guid IdNode);
        public Task<List<TemplateAllowedChildren>> GetChildrensAsync(Guid[] IdTemplateParent);
        public Task InsertChildrenAsync(TemplateAllowedChildren[] template);
        public Task UpdateChildrenAsync(TemplateAllowedChildren[] template);
        public Task DeleteChildrenAsync(Guid IdTemplateParent,Guid IdTemplate);
        public Task DeleteChildrenByIdTemplateAsync(Guid idParent);
    }
    public class TemplateAllowedChildrenData : ITemplateAllowedChildrenData
    {
        private readonly IConfiguration _configuration;
        private readonly IDbConnection _connection;
        public TemplateAllowedChildrenData(IConfiguration configuration, IDbConnection connection)
        {
            _configuration = configuration;
            _connection = connection;
            _connection.ConnectionString = _configuration.GetConnectionString("dbSQL");
        }
		
		#region TemplateAllowedChildren
        public async Task<List<TemplateAllowedChildren>> GetChildrensAsync(Guid IdTemplateParent)
        {
            List<TemplateAllowedChildren> result;
            try
            {
                _connection.Open();
                result = (await _connection.QueryAsync<TemplateAllowedChildren>(TemplateAllowedChildrenQueries.GetTemplateAllowedChildren,new {IdTemplateParent})).ToList();
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateAllowedChildrenData.GetChildrenAsync:", ex);
            }
            return result;
        }
        public async Task<List<TemplateAllowedChildren>> GetChildrenByIdNodeAsync(Guid IdNode)
        {
            List<TemplateAllowedChildren> result;
            try
            {
                _connection.Open();
                result = (await _connection.QueryAsync<TemplateAllowedChildren>(TemplateAllowedChildrenQueries.GetTemplateAllowedChildrenByIdNode,new {IdNode})).ToList();
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateAllowedChildrenData.GetChildrenByIdNodeAsync:", ex);
            }
            return result;
        }
        public async Task<List<TemplateAllowedChildren>> GetChildrensAsync(Guid[] IdTemplateParent)
        {
            List<TemplateAllowedChildren> result;
            try
            {
                _connection.Open();
                result = (await _connection.QueryAsync<TemplateAllowedChildren>(TemplateAllowedChildrenQueries.GetAllowedChildren,new {IdParents=IdTemplateParent})).ToList();
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateAllowedChildrenData.GetChildrenAsync:", ex);
            }
            return result;
        }
        public async Task InsertChildrenAsync(TemplateAllowedChildren[] template)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(TemplateAllowedChildrenQueries.InsertTemplateAllowedChildren, template);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateAllowedChildrenData.InsertChildrenAsync:", ex);
            }
        }
		
        public async Task UpdateChildrenAsync(TemplateAllowedChildren[] template)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(TemplateAllowedChildrenQueries.UpdateTemplateAllowedChildren, template);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateAllowedChildrenData.UpdateChildrenAsync:", ex);
            }
        }
		
		public async Task DeleteChildrenAsync(Guid IdTemplateParent,Guid IdTemplate)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(TemplateAllowedChildrenQueries.DeleteTemplateAllowedChildren, new { IdTemplateParent=IdTemplateParent, IdTemplate = IdTemplate });
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateAllowedChildrenData.DeleteChildrenAsync:", ex);
            }
        }
        public async Task DeleteChildrenByIdTemplateAsync(Guid idParent){
                        try
            {
                _connection.Open();
                await _connection.ExecuteAsync(TemplateAllowedChildrenQueries.DeleteChildrenByIdTemplate, new { IdTemplateParent=idParent });
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateAllowedChildrenData.DeleteChildrenAsync:", ex);
            }
        }
		#endregion
    }
}
