using Agora.DAL.Query;
using Agora.Models;
using Microsoft.Extensions.Configuration;
using Dapper;
using System.Data;

namespace Agora.DAL.Data
{
    public interface ITemplateData
    {
        public Task<List<Template>> GetTemplatesAsync();       
        public Task InsertTemplateAsync(Template template);
        public Task UpdateTemplateAsync(Template template);
        public Task DeleteTemplateAsync(Guid IdTemplate);
    }
    public class TemplateData : ITemplateData
    {
        private readonly IConfiguration _configuration;
        private readonly IDbConnection _connection;
        public TemplateData(IConfiguration configuration, IDbConnection connection)
        {
            _configuration = configuration;
            _connection = connection;
            _connection.ConnectionString = _configuration.GetConnectionString("dbSQL");
        }
		
		#region Template
        public async Task<List<Template>> GetTemplatesAsync()
        {
            List<Template> result = new List<Template>();
            try
            {
                _connection.Open();
                result = (await _connection.QueryAsync<Template>(TemplateQueries.GetTemplate)).ToList();
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateData.GetTemplateAsync:", ex);
            }
            return result;
        }
		
        public async Task InsertTemplateAsync(Template template)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(TemplateQueries.InsertTemplate, template);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateData.InsertTemplateAsync:", ex);
            }
        }
		
        public async Task UpdateTemplateAsync(Template template)
        {
            try
            {
                _connection.Open();
                template.ModifyDate = DateTime.UtcNow;
                await _connection.ExecuteAsync(TemplateQueries.UpdateTemplate, template);
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateData.UpdateTemplateAsync:", ex);
            }
        }
		
		public async Task DeleteTemplateAsync(Guid IdTemplate)
        {
            try
            {
                _connection.Open();
                await _connection.ExecuteAsync(TemplateQueries.DeleteTemplate, new { IdTemplate = IdTemplate });
                _connection.Close();
            }
            catch (Exception ex)
            {
                throw new Exception("Error BBDD TemplateData.DeleteTemplateAsync:", ex);
            }
        }
		#endregion
    }
}
